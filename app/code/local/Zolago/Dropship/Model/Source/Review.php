<?php
class Zolago_Dropship_Model_Source_Review {
	public function toOptionHash() {
		return Mage::getSingleton('udprod/source')->setPath('system_status')->toOptionHash(true);
	}
	public function toOptionArray() {
		return Mage::getSingleton('udprod/source')->setPath('system_status')->toOptionArray();
	}	
}