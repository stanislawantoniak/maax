<?php

class Snowdog_Freshmail_Model_ServiceManager
{
    /**
     * Subscriber statuses in Freshmail
     *
     * @const int
     */
    const SUBSCRIBER_STATUS_SUBSCRIBED = 1;
    const SUBSCRIBER_STATUS_AWAITS_ACTIVATION = 2;
    const SUBSCRIBER_STATUS_NOT_ACTIVE = 3;
    const SUBSCRIBER_STATUS_UNSUBSCRIBED = 4;
    const SUBSCRIBER_STATUS_ERROR = 5;
    const SUBSCRIBER_STATUS_FAILURE = 8;

    /**
     * Api client instance
     *
     * @var Snowdog_Freshmail_Model_Api_Client
     */
    protected $_api;

    /**
     * Logger instance
     *
     * @var Snowdog_Freshmail_Model_Log_Adapter
     */
    protected $_logger;

    /**
     * List fields
     *
     * @var null|array
     */
    protected $_listFields = null;

    /**
     * Init api adapter and logger instance
     */
    public function __construct()
    {
        $this->_logger = Mage::getModel(
            'snowfreshmail/log_adapter',
            'snowfreshmail.log'
        );
    }

    /**
     * Retrieve api client instance
     *
     * @param bool $withApiTest
     *
     * @return Snowdog_Freshmail_Model_Api_Client
     */
    public function getApi($withApiTest = true)
    {
        if (null === $this->_api) {
            /** @var Snowdog_Freshmail_Helper_Api $apiHelper */
            $apiHelper = Mage::helper('snowfreshmail/api');
            if ($withApiTest && !$apiHelper->isConnected()) {
                Mage::throwException(
                    Mage::helper('snowfreshmail')->__('No api connection')
                );
            }
            /** @var Snowdog_Freshmail_Model_Config $configModel */
            $configModel = Mage::getSingleton('snowfreshmail/config');
            $this->_api = Mage::getSingleton('snowfreshmail/api_client')
                ->setApiKey($configModel->getKey())
                ->setApiSecret($configModel->getSecret())
            ;
        }
        return $this->_api;
    }

    /**
     * Ping service to test it
     *
     * @return bool
     */
    public function testConnection()
    {
        try {
            $response = $this->getApi(false)->call('ping');
            if (isset($response['data'])
                && strtoupper($response['data']) == 'PONG') {
                return true;
            }
        } catch (Exception $e) {
            $this->_logger->log($e->getMessage());
        }
        return false;
    }

    /**
     * Get all Freshmail lists
     *
     * @return array
     */
    public function getLists()
    {
        $lists = array();

        try {
            $response = $this->getApi()->call('subscribers_list/lists');
            if (isset($response['lists'])) {
                $lists = $response['lists'];
            } else {
                Mage::throwException('Error while getting list');
            }
        } catch (Exception $e) {
            $this->_logger->log($e->getMessage());
        }

        return $lists;
    }

    /**
     * Edit subscriber
     *
     * @param array $data
     * @param bool  $oldEmail
     */
    public function editSubscriber($data, $oldEmail = null)
    {
        if (!is_null($oldEmail)) {
            $data['old_email'] = $oldEmail;
        }

        $request = Mage::getModel('snowfreshmail/api_request');
        $request->setAction('subscriber_edit');
        $request->setActionParameters($data);
        $request->save();
    }

    /**
     * Update subscriber status
     *
     * @param array $data
     */
    public function updateSubscriberStatus($data)
    {
        $request = Mage::getModel('snowfreshmail/api_request');
        $request->setAction('subscriber_status_update');
        $request->setActionParameters($data);
        $request->save();
    }

    /**
     * Add subscriber
     *
     * @param array $data
     */
    public function addSubscriber($data)
    {
        $request = Mage::getModel('snowfreshmail/api_request');
        $request->setAction('subscriber_add');
        $request->setActionParameters($data);
        $request->save();
    }

    /**
     * Add multiple subscribers
     *
     * @param string    $listHash
     * @param array     $data
     * @param int       $state
     * @param int       $confirm
     *
     * @throws Exception
     */
    public function addMultipleSubscribers($listHash, $data, $state = self::SUBSCRIBER_STATUS_NOT_ACTIVE, $confirm = 0)
    {
        $params = array(
            'list' => $listHash,
            'subscribers' => $data,
            'state' => $state,
            'confirm' => $confirm,
        );
        $this->getApi()->call('subscriber/addMultiple', $params);
    }

    /**
     * Edit multiple subscribers
     *
     * @param string    $listHash
     * @param array     $data
     * @param int       $state
     * @param int       $confirm
     *
     * @throws Exception
     */
    public function editMultipleSubscribers($listHash, $data, $state = self::SUBSCRIBER_STATUS_NOT_ACTIVE, $confirm = 0)
    {
        $params = array(
            'list' => $listHash,
            'subscribers' => $data,
            'state' => $state,
            'confirm' => $confirm,
        );
        $this->getApi()->call('subscriber/editMultiple', $params);
    }

    /**
     * Retrieve multiple subscribers by email
     *
     * @param string    $listHash
     * @param array     $emails
     *
     * @return array
     *
     * @throws Exception
     */
    public function getMultipleSubscribers($listHash, array $emails)
    {
        $subscribers = array();
        foreach ($emails as $email) {
            $subscribers[] = array('email' => $email);
        }
        $params = array('list' => $listHash, 'subscribers' => $subscribers);
        $response = $this->getApi()->call('subscriber/getMultiple', $params);

        return $response;
    }

    /**
     * Delete subscriber
     *
     * @param array $data
     */
    public function deleteSubscriber($data)
    {
        $request = Mage::getModel('snowfreshmail/api_request');
        $request->setAction('subscriber_delete');
        $request->setActionParameters($data);
        $request->save();
    }

    /**
     * Retrieve fields from specified list
     *
     * @param string $listHash
     *
     * @return array
     *
     * @throws Exception
     */
    public function getListFields($listHash)
    {
        if (!isset($this->_listFields[$listHash])) {
            $response = $this->getApi()->call(
                'subscribers_list/getFields',
                array('hash' => $listHash)
            );
            if (!isset($response['fields'])) {
                Mage::throwException('Could not load subscription list fields');
            }
            $this->_listFields[$listHash] = array();
            foreach ($response['fields'] as $field) {
                $this->_listFields[$listHash][$field['tag']] = $field['name'];
            }
        }

        return $this->_listFields[$listHash];
    }

    /**
     * Check list has a field
     *
     * @param string $listHash
     * @param string $tag
     *
     * @return bool
     */
    public function hasField($listHash, $tag)
    {
        $listFields = $this->getListFields($listHash);
        if (!isset($listFields[$tag])) {
            return false;
        }
        return true;
    }

    /**
     * Add a field to the list
     *
     * @param string $listHash
     * @param string $name
     * @param string $fieldType
     *
     * @throws Exception
     */
    public function addField($listHash, $name, $fieldType = 'text')
    {
        $tag = strtolower($name);
        if (!$this->hasField($listHash, $tag)) {
            if ($fieldType == 'numeric') {
                $fieldType = 1;
            } else {
                $fieldType = 0;
            }
            $params = array(
                'hash' => $listHash,
                'name' => $name,
                'tag' => $tag,
                'type' => $fieldType,
            );
            $this->getApi()->call('subscribers_list/addField', $params);
        }
    }
}
