<?php
class Zolago_Dropship_Model_Source_Store {
	public function toOptionHash() {
		return Mage::getSingleton('adminhtml/system_store')->getStoreOptionHash(true);
	}
}
