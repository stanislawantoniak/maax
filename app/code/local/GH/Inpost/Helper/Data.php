<?php
class GH_Inpost_Helper_Data extends Mage_Core_Helper_Abstract {

	public function getInpostLockerName(Unirgy_Dropship_Model_Po $po) {
		return $po->getInpostLockerName();
	}

}