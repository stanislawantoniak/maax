<?php

require_once Mage::getModuleDir('controllers', 'ZolagoOs_OmniChannelVendorAskQuestion') . "/VendorController.php";

class Zolago_DropshipVendorAskQuestion_VendorController extends ZolagoOs_OmniChannelVendorAskQuestion_VendorController
{
	public function questionEditAction() {
		$id = $this->getRequest()->getParam('id');
		$question = Mage::getModel('udqa/question')->load($id);
		$questionVendorId = $question->getVendorId();
		$session = Mage::getSingleton('udropship/session');
		$vendor = $session->getVendor();
		
		if($questionVendorId!=$vendor->getId() && 
				!in_array($questionVendorId, $vendor->getChildVendorIds())){
			$session->addError(Mage::helper('udqa')->__('Question not found'));
			return $this->_redirectReferer();
		}
		return parent::questionEditAction();
	}
	
	public function questionPostAction()
	{
		$session = Mage::getSingleton('udropship/session');

		if ($data = $this->getRequest()->getPost('question')) {
			$id = $this->getRequest()->getParam('id');

			try {
				$question = Mage::getModel('udqa/question')->load($id);

				$vendor = $session->getVendor();
				/* @var $vendor Zolago_Dropship_Model_Vendor */
				$questionVendorId = $question->getVendorId();

				if($questionVendorId!=$vendor->getId() && 
						!in_array($questionVendorId, $vendor->getChildVendorIds())){
					Mage::throwException('Question not found');
				}
				
				if($question->getAnswerText()){
					Mage::throwException('Question answered!');
				}

				/*
				if (!$question->validateVendor($session->getVendorId())) {
					Mage::throwException('Question not found');
				}
				 */

				if ($this->getRequest()->getParam('send_email')) {
					$question->setIsCustomerNotified(0);
				}

				$question->addData($data)->save();

				$session->addSuccess($this->__('Question was successfully saved'));
				$session->setUdqaData(false);

				$this->_redirectQuestionAfterPost();

				return;
			} catch (Exception $e) {
				$session->addError($e->getMessage());
				Mage::logException($e);
				$session->setUdqaData($data);
				$this->_redirectQuestionAfterPost();
				return;
			}
		}
		$session->addError($this->__('Unable to find a data to save'));
		$this->_redirectQuestionAfterPost();
	}
}