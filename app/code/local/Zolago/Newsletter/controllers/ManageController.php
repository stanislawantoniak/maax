<?php
require_once Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php';
class Zolago_Newsletter_ManageController extends Mage_Newsletter_ManageController {
	public function saveAction() {
		if (!$this->_validateFormKey()) {
			return $this->_redirect('customer/account/');
		}
		try {
			$isSubscribed = (boolean)$this->getRequest()->getParam('is_subscribed', false) ? 1 : 0;
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$customer
				->setStoreId(Mage::app()->getStore()->getId())
				->setIsSubscribed($isSubscribed)
				->save();

			if($isSubscribed && $customer->getConfirmMsg()) {
				Mage::getSingleton('customer/session')->addSuccess($this->__("Your subscribtion has been saved.<br />To start receiving our newsletter you have to confirm your e-mail by clicking confirmation link in e-mail that we have just sent to you.<br />Newsletter setting in your account will be changed after e-mail confirmation."));
			} elseif($isSubscribed) {
				Mage::getSingleton('customer/session')->addSuccess($this->__("The subscription has been saved."));
			} elseif (!$isSubscribed) {
				Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
			}
		}
		catch (Exception $e) {
			Mage::logException($e);
			Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
		}
		$this->_redirectReferer();
	}
}