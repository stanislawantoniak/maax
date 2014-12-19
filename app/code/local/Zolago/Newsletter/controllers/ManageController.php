<?php
require_once Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php';
class Zolago_Newsletter_ManageController extends Mage_Newsletter_ManageController {
	public function saveAction() {
		if (!$this->_validateFormKey()) {
			return $this->_redirect('customer/account/');
		}
		$session = Mage::getSingleton('customer/session');
		try {
			$isSubscribed = (boolean)$this->getRequest()->getParam('is_subscribed', false);
			$session->getCustomer()
				->setStoreId(Mage::app()->getStore()->getId())
				->setIsSubscribed($isSubscribed)
				->save();
			if (!$isSubscribed) {
				$session->addSuccess($this->__('The subscription has been removed.'));
			} else {
				//todo: check if customer subscription status is changed if changed then check subscriber status and add correct message
				//$this->__("Your subscribtion has been saved. To start receiving our newsletter you have to confirm your email by clicking
				//confirmation link in e-mail that we have just sent to you. Checkbox below will be checked after confirmation.")
				//Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been saved.'));
			}
		}
		catch (Exception $e) {
			$session->addError($this->__('An error occurred while saving your subscription.'));
		}
		$this->_redirectReferer();
	}
}