<?php

/**
 * Class ZolagoOs_Pwr_Helper_Data
 */
class ZolagoOs_Pwr_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * @return bool
	 */
	public function isActive() {
		return (bool)Mage::getStoreConfig('carriers/zospwr/active');
	}

	/**
	 * @return mixed
	 */
	public function getApiWsdl() {
		return Mage::getStoreConfig('carriers/zospwr/api');
	}

	/**
	 * @return mixed
	 */
	public function getPartnerId() {
		return Mage::getStoreConfig('carriers/zospwr/partner_id');
	}

	/**
	 * @return mixed
	 */
	public function getPartnerKey() {
		return Mage::getStoreConfig('carriers/zospwr/partner_key');
	}
	
}