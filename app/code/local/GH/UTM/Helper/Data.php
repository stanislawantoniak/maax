<?php

/**
 * Class GH_UTM_Helper_Data
 */
class GH_UTM_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * @return bool|Mage_Customer_Model_Customer
	 */
	public function getCustomer() {
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');
		$customerId = $customerSession->getCustomerId();
		if($customerId) {
			/** @var Mage_Customer_Model_Customer $customer */
			$customer = Mage::getModel('customer/customer')->load($customerId);
			if($customer->getId()) {
				return $customer;
			}
		}
		return false;
	}
	
	public function getTime() {
		return Mage::getSingleton('core/date')->gmtTimestamp();
	}

	/**
	 * returns seconds for cookie expiry date
	 * @return int
	 */
	public function getCookieExpiry() {
		$configData = Mage::getStoreConfig(GH_UTM_Model_Source::GHUTM_CONFIG_PATH_COOKIE_DAYS);
		return (is_numeric($configData) ? $configData : GH_UTM_Model_Source::GHUTM_COOKIE_DAYS_DEFAULT)  * 24 * 60 * 60; //in seconds
	}

	/**
	 * returns array of utm_source exceptions
	 * @return array
	 */
	public function getExceptions() {
		return explode(",",Mage::getStoreConfig(GH_UTM_Model_Source::GHUTM_CONFIG_PATH_EXCEPTIONS));
	}

	public function setUtmCustomer(Mage_Customer_Model_Customer $customer) {
		$utmData = json_decode($customer->getUtmData(),1);
		//todo: finish this

	}

}