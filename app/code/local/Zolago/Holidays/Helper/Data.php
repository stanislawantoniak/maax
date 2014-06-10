<?php
class Zolago_Holidays_Helper_Data extends Mage_Core_Helper_Abstract{
	
	public function booleanToValie($value){
		return ($value == 1) ? "Yes" : "No";
	}
}
