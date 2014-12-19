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
			}
		}
		catch (Exception $e) {
			$session->addError($this->__('An error occurred while saving your subscription.'));
		}
		$this->_redirectReferer();
	}
}