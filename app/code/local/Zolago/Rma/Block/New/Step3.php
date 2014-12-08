<?php
class Zolago_Rma_Block_New_Step3 extends  Zolago_Rma_Block_New_Abstract{
	private $_localeSet = false;
	/**
	 * @param Integer $num
	 * @returns String
	 */
	public function getWeekday($num = 0) {
		if(!$this->_localeSet) {
			$locale = new Zend_Locale(Mage::app()->getLocale()->getLocaleCode());
			Zend_Registry::set('Zend_Locale', $locale);
			$this->_localeSet = true;
		}

		$date = new Zend_Date("04-01-1970","dd-mm-yyyy"); //first sunday in unix timestamp
		return $date->add($num,Zend_Date::DAY)->toString(Zend_Date::WEEKDAY);
	}
}