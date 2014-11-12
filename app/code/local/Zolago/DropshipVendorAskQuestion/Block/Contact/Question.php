<?php
class Zolago_DropshipVendorAskQuestion_Block_Contact_Question extends Mage_Core_Block_Template {
	
	
	/**
	 * @return string
	 */
	public function getFormUrl() {
		return $this->getUrl("udqa/customer/post");
	}
	
	/**
	 * @return string
	 */
	public function getFormKey() {
		return Mage::getSingleton('core/session')->getFormKey();
	}
	
	/**
	 * @return bool
	 */
	public function isLoggedIn() {
		return $this->getSession()->isLoggedIn();
	}
	
	/**
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer() {
		return $this->getSession()->getCustomer();
	}
	
	/**
	 * @return Mage_Customer_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('customer/session');
	}
	
}
