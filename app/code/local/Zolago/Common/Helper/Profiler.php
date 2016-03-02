<?php
class Zolago_Common_Helper_Profiler extends Mage_Core_Helper_Abstract {
	
	protected $_timestamp;
	protected $_sum;
	
	public function start() {
		$this->_timestamp = $this->_getMicrotime();
		$this->_sum = 0;
	}
	
	
	public function log($string, $rest=true) {
		$currentTimestamp = $this->_getMicrotime();
		$delay = $currentTimestamp - $this->_timestamp;
		if($rest){
			$this->_timestamp = $currentTimestamp;
			$this->_sum += $delay;
		}
		Mage::log($this->_format($string, $delay));
	}
	
	protected function _getMicrotime(){ 
		list($usec, $sec) = explode(" ",microtime()); 
		return ((float)$usec + (float)$sec); 
    } 
	
	protected function _format($string, $delay) {
		return sprintf("%s: %fs (sum: %fs)", $string, $delay, $this->_sum);
	}
}