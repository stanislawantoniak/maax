<?php

/**
 * helper for gh_statements
 */
class GH_Statements_Helper_Data extends Mage_Core_Helper_Abstract {

	public static function getTax() {
		return floatval(str_replace(',','.', Mage::getStoreConfig('ghstatements/general/tax_for_commission')));
	}
}