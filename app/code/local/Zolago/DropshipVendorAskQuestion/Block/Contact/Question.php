<?php
class Zolago_DropshipVendorAskQuestion_Block_Contact_Question extends Mage_Core_Block_Template {
	
	
	/**
	 * @return string
	 */
	public function getFormUrl($secure=null) {
		if(is_null($secure)){
			$secure = Mage::app()->getStore()->isCurrentlySecure();
		}
		return $this->getUrl("udqa/customer/post", array("_secure"=>$secure));
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
