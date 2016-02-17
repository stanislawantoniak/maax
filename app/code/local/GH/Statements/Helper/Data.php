<?php

/**
 * helper for gh_statements
 */
class GH_Statements_Helper_Data extends Mage_Core_Helper_Abstract {

	public static function getTax() {
		return floatval(str_replace(',','.', Mage::getStoreConfig('ghstatements/general/tax_for_commission')));
	}

	/**
	 * Retrieve actual charge_commission flag for store from dotpay config
	 *
	 * @param null|string|bool|int|Mage_Core_Model_Store $store
	 * @return bool
	 */
	public static function getDotpayChargeCommissionFlag($store = null) {
		$flag = Mage::app()->getStore($store)->getConfig('payment/dotpay/charge_commission_flag');
		return (bool) $flag;
	}
}