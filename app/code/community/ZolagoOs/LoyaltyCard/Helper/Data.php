<?php

/**
 * Class ZolagoOs_LoyaltyCard_Helper_Data
 */
class ZolagoOs_LoyaltyCard_Helper_Data extends Mage_Core_Helper_Abstract {

	/**
	 * Configuration array for attaching customer to group depends by his loyalty cards
	 *
	 * @param null $store
	 * @return array
	 */
	public function getLoyaltyCardConfig($store = null) {
		$config = Mage::getStoreConfig('customer/loyalty_card/config', $store);
		$config = (array)json_decode($config, true);
		return (array)$config;
	}
	
	public function saveLog($string) {
		Mage::log($string, null, 'loyalty_card.log');
	}
}