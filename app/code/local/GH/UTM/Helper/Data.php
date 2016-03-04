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
		$exceptions = explode(",",Mage::getStoreConfig(GH_UTM_Model_Source::GHUTM_CONFIG_PATH_EXCEPTIONS));
		foreach($exceptions as $k=>$exception) {
			$exceptions[$k] = trim($exception);
		}
		return $exceptions;
	}

	public function updateUtmData($utmDataJson) {
		$newUtmData = json_decode($utmDataJson,1);
		$exceptions = $this->getExceptions();
		/** @var Zolago_Customer_Model_Session $customerSession */
		$customerSession = Mage::getSingleton('customer/session');
		/** @var Zolago_Customer_Model_Customer $customer */
		$customer = $customerSession->getCustomer();
		
		//handle exceptions
		if(!empty($exceptions) &&
			isset($newUtmData['utm_source']) &&
			in_array($newUtmData['utm_source'],$exceptions) &&
			(($customer->getId() && $customer->getUtmData()) || $this->getCookie())
		) {
			return false;
		}

		//set time
		$cookieExpiry = Mage::getModel('core/date')->gmtTimestamp() + $this->getCookieExpiry();
		if(!isset($newUtmData[GH_UTM_Model_Source::GHUTM_DATE_NAME]) || !$newUtmData[GH_UTM_Model_Source::GHUTM_DATE_NAME]) {
			$newUtmData[GH_UTM_Model_Source::GHUTM_DATE_NAME] = $cookieExpiry;
		}

		$newUtmDataJson = json_encode($newUtmData);

		if($customer->getId()) {
			$customer->setUtmData($newUtmDataJson)->save();
			/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
			$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
			$cacheHelper->removeCacheCustomerUtmData();
		}

		setcookie(GH_UTM_Model_Source::GHUTM_COOKIE_NAME,$newUtmDataJson,$cookieExpiry,"/");

		return true;
	}

	public function getCookie() {
		if(isset($_COOKIE[GH_UTM_Model_Source::GHUTM_COOKIE_NAME])) {
			return $_COOKIE[GH_UTM_Model_Source::GHUTM_COOKIE_NAME];
		}
		return '';
	}

}