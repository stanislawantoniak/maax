<?php
require_once Mage::getModuleDir('controllers','Mage_Newsletter').DS.'ManageController.php';
class Zolago_Newsletter_ManageController extends Mage_Newsletter_ManageController {
	public function saveAction() {
		if (!$this->_validateFormKey()) {
			return $this->_redirect('customer/account/');
		}
		try {
			$isSubscribed = (boolean)$this->getRequest()->getParam('is_subscribed', false) ? 1 : 0;
			Mage::getSingleton('customer/session')->getCustomer()
				->setStoreId(Mage::app()->getStore()->getId())
				->setIsSubscribed($isSubscribed)
				->save();

			if (!$isSubscribed) {
				Mage::getSingleton('customer/session')->addSuccess($this->__('The subscription has been removed.'));
			}
		}
		catch (Exception $e) {
			Mage::getSingleton('customer/session')->addError($this->__('An error occurred while saving your subscription.'));
		}
		$this->_redirectReferer();
	}
}