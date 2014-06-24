<?php
class Zolago_Holidays_Helper_DateCalculator extends Mage_Core_Helper_Abstract{
	
	protected $weekend;
	
	protected $country_id;
	protected $exclude_from_pickup;
	protected $exclude_from_delivery;
	
	/**
	 * Calculate maximum shipping date for PO
	 * 
	 * @param Zolago_Po_Model_Po $po
	 * @param boolean $return_object Set to TRUE if you want to return Z
	 * 
	 * @return Zend_Date|string
	 */
	public function calculateMaxPoShippingDate(Zolago_Po_Model_Po $po, $return_object = FALSE){
		
		$store = $po->getStore();
		$storeId = $store->getStoreId();
		$locale = Mage::getStoreConfig('general/locale/code', $store->getId());
		$locale_array = explode("_", $locale);
		
		$vendor = $po->getVendor();
		
		$this->weekend = explode(',', Mage::getStoreConfig('general/locale/weekend', $storeId));
		$this->country_id = (key_exists(1, $locale_array)) ? $locale_array[1] : NULL;
		$this->exclude_from_delivery = 1;
		$this->exclude_from_pickup = array(1, 0);
		
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
	
	/**
	 * @param int $max_days
	 * @param mixed $max_time
	 * @param timestamp $current_timestamp
	 * 
	 * @return timestamp
	 */
	protected function calculateMaxDate($max_days, $max_time, $current_timestamp = NULL){
		
		// Calculate number of days based on hour
		if(!$current_timestamp){
			$current_timestamp = Mage::getModel('core/date')->timestamp(time());
		}
		$current_hour = date("H", $current_timestamp);
		$current_minute = date("i", $current_timestamp);
		$max_hour_array = explode(",", $max_time);
		$max_hour = $max_hour_array[0];
		$max_minute = $max_hour_array[1];
		
		if((($current_hour * 60) + $current_minute) > (($max_hour * 60) + $max_minute)){
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
	
	/**
	 * @param timestamp $timestamp
	 * 
	 * @return boolean
	 */
	private function _isWeekend($timestamp){
		$weekd_day = date('w', $timestamp);
		return in_array($weekd_day, $this->weekend);
	}
	
	/**
	 * @param timestamp $timestamp
	 * 
	 * @return boolean
	 */
	private function _isHoliday($timestamp){
		
		$fixed_string = date("d/m/Y", $timestamp);
		$movable_string = date("d/m", $timestamp);
		
		$collection = Mage::getModel('zolagoholidays/holiday')->getCollection();
		$collection->addFieldToFilter('date', array($fixed_string, $movable_string));
		
		if($this->country_id){
			$collection->addFieldToFilter('country_id', $this->country_id);
		}
		
		$collection->addFieldToFilter('exclude_from_delivery', $this->exclude_from_delivery);
		
		$collection->addFieldToFilter('exclude_from_pickup', $this->exclude_from_pickup);
		
		return ($collection->count() > 0) ? true : false;
	}
}

