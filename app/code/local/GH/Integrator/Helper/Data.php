<?php

class GH_Integrator_Helper_Data extends Mage_Core_Helper_Abstract {
	public function getDescriptionHours() {
		return $this->valueToTimeArray(Mage::getStoreConfig('ghintegrator/hours/description'));
	}
	public function getPriceHours() {
		return $this->valueToTimeArray(Mage::getStoreConfig('ghintegrator/hours/price'));
	}
	public function getStockHours() {
		return $this->valueToTimeArray(Mage::getStoreConfig('ghintegrator/hours/stock'));
	}
	private function valueToTimeArray($value) {
		if($value) {
			$values = explode(',',$value);
			$return = array();
			foreach($values as $time) {
				if($currentTime = $this->isTime($time)) {
					$return[] = $time;
				}
			}
			if(count($return)) {
				return $return;
			}
		}
		return array();
	}
	private function isTime($time) {
		if (preg_match("/(2[0-3]|[01][0-9]|[0-9]):([0-5][0-9])/", $time)) {
			$timeArr = explode(":",$time);
			if(strlen($timeArr[0]) == 1) {
				$timeArr[0] = "0".$timeArr[0];
				$time = implode(":",$timeArr);
			}
			return $time;
		}
		return false;
	}
}