<?php

class Snowdog_Freshmail_IndexController
    extends Mage_Core_Controller_Front_Action
{
    /**
     * Logger instance
     *
     * @var Snowdog_Freshmail_Model_Log_Adapter
     */
    protected $_logger;

    /**
     * Retrieve logger instance
     *
     * @return Snowdog_Freshmail_Model_Log_Adapter
     */
    protected function _getLogger()
    {
        if (is_null($this->_logger)) {
            $this->_logger = Mage::getModel(
                'snowfreshmail/log_adapter',
                'snowfreshmail.log'
            );
        }
        return $this->_logger;
    }

    /**
     * Popup save action
     * 
     * @return array
     */
    public function popupAction()
    {
        if (!$this->getRequest()->isAjax()) {
            $this->norouteAction();
            return;
        }
        $email = $this->getRequest()->getPost('email');
        /** @var Snowdog_Freshmail_Model_Config $configModel */
        $configModel = Mage::getSingleton('snowfreshmail/config');

        if (trim($email) == '') {
            $this->_sendErrorResponse($configModel->getEmptyFieldValue());
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->_sendErrorResponse($configModel->getInvalidEmailMessage());
            return;
        }

        $subscriber = Mage::getModel('newsletter/subscriber')
            ->loadByEmail($email);
        if ($subscriber->isSubscribed()) {
            $this->_sendErrorResponse(
                $configModel->getAlreadySubscribedMessage()
            );
            return;
        }

        try {
            Mage::register('snowfreshmail_disable_event', true);
            $subscriber->subscribe($email);
            Mage::register('snowfreshmail_disable_event', false, true);

            if ($subscriber->getId() && !Mage::helper('customer')->isLoggedIn()) {
                $config = Mage::getSingleton('snowfreshmail/config')->getCustomFieldMappings();
                $customData = array();
                foreach ($config as $map) {
                    if (!$map['use_in_form']) {
                        continue;
                    }
                    $customData[$map['source_field']] = $this->getRequest()->getPost($map['source_field']);
                }
                $resourceModel = Mage::getSingleton('core/resource');
                $adapter = $resourceModel->getConnection('core_write');
                $adapter->insertOnDuplicate(
                    $resourceModel->getTableName('snowfreshmail/custom_data'),
                    array('subscriber_id' => $subscriber->getId(), 'subscriber_data' => serialize($customData)),
                    array('subscriber_data')
                );

                /** @var Snowdog_Freshmail_Helper_Api $apiHelper */
                $apiHelper = Mage::helper('snowfreshmail/api');
                $data = $subscriber->getData();
                $data = array_merge($data, $customData);
                $customFields = $apiHelper->convertSubscriberData(
                    $subscriber->getSubscriberEmail(),
                    $data,
                    $subscriber->getStoreId()
                );

                /** @var Snowdog_Freshmail_Model_Config $configModel */
                $configModel = Mage::getSingleton('snowfreshmail/config');
                $data = array(
                    'list' => $configModel->getListHash($subscriber->getStoreId()),
                    'email' => $subscriber->getSubscriberEmail(),
                    'custom_fields' => $customFields,
                );
                $serviceManager = Mage::getSingleton('snowfreshmail/serviceManager');
                $serviceManager->editSubscriber($data);
            }

            $result = array(
                'status' => 'success',
                'message' => $configModel->getSubmissionSuccessMessage(),
            );
            $this->_sendJsonResponse($result);
        } catch (Exception $e) {
            $this->_getLogger()->log($e->getMessage());
            $this->_sendErrorResponse(
                $configModel->getSubmissionFailureMessage()
            );
            return;
        }
    }

    /**
     * Send error response
     *
     * @param string $message
     */
    protected function _sendErrorResponse($message)
    {
        $result = array(
            'status' => 'error',
            'message' => $message,
        );
        $this->_sendJsonResponse($result);
    }

    /**
     * Send json response
     *
     * @param array $body
     */
    protected function _sendJsonResponse($body)
    {
        $this->getResponse()->clearHeaders()
            ->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($body));
    }
}
