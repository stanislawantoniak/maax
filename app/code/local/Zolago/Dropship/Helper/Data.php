<?php
class Zolago_Dropship_Helper_Data extends Unirgy_Dropship_Helper_Data {
	public function isUdpoMpsAvailable($carrierCode, $vendor = null) {
		if(in_array($carrierCode, array("zolagodhl"))){
			return true;
		}
		return parent::isUdpoMpsAvailable($carrierCode, $vendor);
	}
}