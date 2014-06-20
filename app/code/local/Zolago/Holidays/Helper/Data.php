<?php
class Zolago_Holidays_Helper_Data extends Mage_Core_Helper_Abstract{
	
	/**
	 * @param int $value 
	 */
	public function booleanToValue($value){
		return ($value == 1) ? "Yes" : "No";
	}
}
