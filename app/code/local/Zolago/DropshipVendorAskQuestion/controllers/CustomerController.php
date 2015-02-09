<?php

require_once Mage::getConfig()->getModuleDir('controllers', 'Unirgy_DropshipVendorAskQuestion') 
		. DS . "CustomerController.php";


class Zolago_DropshipVendorAskQuestion_CustomerController extends Unirgy_DropshipVendorAskQuestion_CustomerController
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

        $cSess = Mage::getSingleton('customer/session');

        $customer = $cSess->getCustomer();

        $error = false;
        if (!empty($data)) {
            $session = empty($question['product_id'])
                ? Mage::getSingleton('udqa/session')
                : Mage::getSingleton('catalog/session');
            unset($question['question_id']);
            $qModel   = Mage::getModel('udqa/question')
                ->setData($question)
                ->setQuestionDate(now());
            if ($cSess->isLoggedIn()) {
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
                    $error = true;
                    $this->_saveFormData($data);
                    $session->addError($this->__('Unable to post the question. 2'));
                }
            }
            else {
                $error = true;
                $this->_saveFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to post the question. 1'));
                }
                Mage::getSingleton('udqa/session')->setDataPopulate( $question );
            }
        }


        //END PARRENT::POSTACTION

        $this->_redirectReferer();



		// Force redirection if flag setted
		if($this->getRequest()->getParam("redirect_referer")){
			$this->_redirectReferer();
		}

		return $this;
    }
}