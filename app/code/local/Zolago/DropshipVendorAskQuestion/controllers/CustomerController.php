<?php

require_once Mage::getConfig()->getModuleDir('controllers', 'ZolagoOs_OmniChannelVendorAskQuestion') 
		. DS . "CustomerController.php";


class Zolago_DropshipVendorAskQuestion_CustomerController extends ZolagoOs_OmniChannelVendorAskQuestion_CustomerController
{
    public function postAction()
    {
        //PARRENT::POSTACTION

        if (!$this->getRequest()->isPost() && ($data = $this->_fetchFormData())) {
            $question = array();
            if (isset($data['question']) && is_array($data['question'])) {
                $question = $data['question'];
            }
        } else {
            $data   = $this->getRequest()->getPost();
            $question = $this->getRequest()->getParam('question', array());
        }
		
		if(isset($question['shipment_id']) && empty($question['shipment_id'])){
			unset($question['shipment_id']);
		}
        if(!isset($question['po_id']) || !$question['po_id']) {
            $question['po_id'] = empty($question['order_id']) ? null : $question['order_id']; //don't know why it's here so leaving to ensure compatibility
        } else {
            /** @var Zolago_Po_Model_Po $po */
            $po = Mage::getModel('zolagopo/po')->load($question['po_id']);
            if(!$po->getId() || $po->getContactToken() != $question['po_contact_token']) {
                $question['po_id'] = null;
            }
        }

        /** @var Zolago_Customer_Model_Session $cSess */
        $cSess = Mage::getSingleton('customer/session');

        /** @var Zolago_Customer_Model_Customer $customer */
        $customer = $cSess->getCustomer();

        if (!empty($data)) {
            $session = Mage::getSingleton('catalog/session');
            unset($question['question_id']);
            /** @var Zolago_DropshipVendorAskQuestion_Model_Question $qModel */
            $qModel   = Mage::getModel('udqa/question')
                ->setData($question)
                ->setQuestionDate(now())
                ->setStoreId(Mage::app()->getStore()->getStoreId());
            if ($cSess->isLoggedIn() && !isset($question['customer_id'])) {
                $qModel
                    ->setCustomerEmail($customer->getEmail())
                    ->setCustomerName($customer->getFirstname().' '.$customer->getLastname())
                    ->setCustomerId($customer->getId());
            }
            $validate = $qModel->validate();
            if ($validate === true) {
                try {
                    $qModel->save();
                    $session->addSuccess($this->__('Your question has been accepted for moderation.'));
                }
                catch (Exception $e) {
                    $this->_saveFormData($data);
                    $session->addError($this->__('Unable to post the question.'));
                }
            }
            else {
                $this->_saveFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post the question.'));
                }
                Mage::getSingleton('udqa/session')->setDataPopulate( $question );
            }
        }


        //END PARRENT::POSTACTION
        $obj = Zend_Uri_Http::fromString($this->_getRefererUrl());
        $obj->addReplaceQueryParameters(array('salt'=>uniqid()));
        $this->getResponse()->setRedirect($obj->getUri());
	return $this;
    }
}