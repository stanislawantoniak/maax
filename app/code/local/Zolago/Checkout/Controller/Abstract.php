<?php
/**
 * Define multi logic here
 */
require_once Mage::getConfig()->getModuleDir("controllers", "Mage_Checkout") . DS . "OnepageController.php";

abstract class Zolago_Checkout_Controller_Abstract extends Mage_Checkout_OnepageController{
	
	/**
	 * @return Mage_Customer_Model_Session
	 */
	protected function _getCustomerSession() {
		 return Mage::getSingleton('customer/session');
	}
	
	/**
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getCheckoutSession() {
		 return Mage::getSingleton('checkout/session');
	}
}