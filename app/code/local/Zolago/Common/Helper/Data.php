<?php
class Zolago_Common_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getCarriersForVendor() {
		return array("", "custom", "zolagodhl");
	}
}