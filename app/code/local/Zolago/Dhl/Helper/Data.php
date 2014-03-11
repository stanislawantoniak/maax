<?php

/**
 * helper for dhl module
 */
class Zolago_Dhl_Helper_Data extends Mage_Core_Helper_Abstract {
    public function isDhlEnabledForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		return (bool)(int)$vendor->getUseDhl();
	}
    public function isDhlEnabledForPos(Zolago_Pos_Model_Pos $pos) {
		return (bool)(int)$pos->getUseDhl();
	}
}