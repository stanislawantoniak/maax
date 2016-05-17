<?php
class Zolago_Customer_Helper_Data extends Mage_Core_Helper_Abstract {
    public function generateToken() {
        return hash("sha256", uniqid(microtime()));
    }

    public function getPasswordMinLength() {
        return 6; //it's hardcoded in app/code/core/Mage/Customer/Model/Customer/Attribute/Backend/Password.php
    }

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
}