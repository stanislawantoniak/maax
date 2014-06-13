<?php
class Zolago_Holidays_Helper_DateCalculator extends Mage_Core_Helper_Abstract{
	
	protected $weekend;
	
	public function calculateMaxPoShippingDate(Zolago_Po_Model_Po $po, $return_object = FALSE){
		
		$store = $po->getStore();
		$storeId = $store->getStoreId();
		$locale = $store->getLocaleCode();
		$vendor = $po->getVendor();
		
		$this->weekend = explode(',', Mage::getStoreConfig('general/locale/weekend', $storeId));
		
		$timezone = Mage::getStoreConfig('general/locale/timezone', $storeId);
		$max_shipping_days = $vendor->getMaxShippingDays($storeId);
		$max_shipping_time = $vendor->getMaxShippingTime($storeId);
		
		if(!$max_shipping_days || !$max_shipping_time){
			Mage::log("No global values set to Max Shipping Days and Max Shippint Time");
			return NULL;	
		}
		
		$max_date_timestamp = $this->calculateMaxDate($max_shipping_days, $max_shipping_time, strtotime($po->getCreatedAt()));
		
		$date = new Zend_Date($max_date_timestamp, null, $locale);
	    $date->setTimezone($timezone);
        $date->setHour(0)
            ->setMinute(0)
            ->setSecond(0);
		
		if($return_object){
			return $date;
		}
		else {
			return $date->toString('dd/MM/yyyy');
		}
	} 
	
	protected function calculateMaxDate($max_days, $max_time, $current_timestamp = NULL){
		
		// Calculate number of days based on hour
		if(!$current_timestamp){
			$current_timestamp = Mage::getModel('core/date')->timestamp(time());
		}
		$current_hour = date("H", $current_timestamp);
		$max_hour_array = explode(",", $max_time);
		$max_hour = $max_hour_array[0];
		
		if($current_hour > $max_hour){
			$max_days++;
		}
		
		for($i = 0; $i < $max_days; $i++){
			
			$next_day = strtotime("+ " . $i . "days", $current_timestamp);
			
			// Check if is a weekend
			if($this->_isWeekend($next_day)){
				$max_days++;
				continue;
			}
			
			// Check if is a holiday
			if($this->_isHoliday($next_day)){
				$max_days++;
			}
		}
		
		return strtotime("+ " . ($max_days - 1) . "days", $current_timestamp);
	}
	
	private function _isWeekend($timestamp){
		$weekd_day = date('w', $timestamp);
		return in_array($weekd_day, $this->weekend);
	}
	
	private function _isHoliday($timestamp){
		
		$fixed_string = date("d/m/Y", $timestamp);
		$movable_string = date("d/m", $timestamp);
		
		$holiday = Mage::getModel('zolagoholidays/holiday')->getCollection()
												           ->addFieldToFilter('date', array($fixed_string, $movable_string));
														   
		return ($holiday->count() > 0) ? true : false;
	}
}

