<?php

/**
 * Class GH_UTM_Model_UTM
 */
class GH_UTM_Model_Observer {
	public function customerLogin($observer) {
		/** @var Zolago_Customer_Model_Customer $customer */
		$customer = $observer->getCustomer();
		
		/** @var GH_UTM_Helper_Data $utmHelper */
		$utmHelper = Mage::helper('ghutm');
		
		$finalUtm = false;
		$cookieUtm = $utmHelper->getCookie() ? json_decode($utmHelper->getCookie(),1) : false;
		$customerUtm = $customer->getUtmData() ? json_decode($customer->getUtmData(),1) : false;
		
		if($cookieUtm && $customerUtm) {
			$exceptions = $utmHelper->getExceptions();
			
			$cookieUtmException = isset($cookieUtm['utm_source']) && in_array($cookieUtm['utm_source'],$exceptions);
			$customerUtmException = isset($customerUtm['utm_source']) && in_array($customerUtm['utm_source'],$exceptions);
			
			if(($cookieUtmException && $customerUtmException) || (!$cookieUtmException && !$customerUtmException)) {
				$cookieUpdated = isset($cookieUtm[GH_UTM_Model_Source::GHUTM_DATE_NAME]) ? $cookieUtm[GH_UTM_Model_Source::GHUTM_DATE_NAME] : false;
				$customerUpdated = isset($customerUtm[GH_UTM_Model_Source::GHUTM_DATE_NAME]) ? $customerUtm[GH_UTM_Model_Source::GHUTM_DATE_NAME] : false; 
				if(!$cookieUpdated) {
					$finalUtm = $customerUtm;
				} elseif(!$customerUpdated) {
					$finalUtm = $cookieUtm;
				} else {
					$finalUtm = $cookieUpdated >= $customerUpdated ? $cookieUtm : $customerUtm;
				}
			} elseif($cookieUtmException) {
				$finalUtm = $customerUtm;
			} elseif($customerUtmException) {
				$finalUtm = $cookieUtm;
			}
		} elseif(!$cookieUtm) {
			$finalUtm = $customerUtm;
		} elseif(!$customerUtm) {
			$finalUtm = $cookieUtm;
		}
		
		if($finalUtm) {
			$finalUtmJson = json_encode($finalUtm);
			$customer->setUtmData($finalUtmJson)->save(); 
			/** @var Orba_Common_Helper_Ajax_Customer_Cache $cacheHelper */
			$cacheHelper = Mage::helper('orbacommon/ajax_customer_cache');
			$cacheHelper->removeCacheCustomerUtmData();
			setcookie(GH_UTM_Model_Source::GHUTM_COOKIE_NAME,$finalUtmJson,$finalUtm[GH_UTM_Model_Source::GHUTM_DATE_NAME],"/");
		}
	}
}
