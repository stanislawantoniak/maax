<?php
class Zolago_Holidays_Helper_Data extends Mage_Core_Helper_Abstract{
	
	/**
	 * @param int $value 
	 */
	public function booleanToValue($value){
		return ($value == 1) ? "Yes" : "No";
	}
	
	/**
	 * @return string|null
	 */
	public function getCurrentCountryId(){
		
		$country_id = NULL;
		
		$locale = Mage::app()->getLocale()->getLocaleCode();
		$locale_array = explode("_", $locale);
		if(is_array($locale_array) && key_exists(1, $locale_array)){
			$country_id = $locale_array[1];
		}
		
		return $country_id;
	}
	
}
