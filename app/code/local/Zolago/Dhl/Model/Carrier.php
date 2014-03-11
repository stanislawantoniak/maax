<?php
/**
 * Dhl carrier 
 */
class Zolago_Dhl_Model_Carrier extends 
        Mage_Shipping_Model_Carrier_Abstract implements 
        Mage_Shipping_Model_Carrier_Interface{ 
			
    protected $_code = "zolagodhl";
	
	/**
	 * Empyt collect
	 * @param Mage_Shipping_Model_Rate_Request $request
	 * @return type
	 */
	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
        return  Mage::getModel('shipping/rate_result');
	}
	
	/**
	 * @return array
	 */
	public function getAllowedMethods() {
		return array();
	}   
	/**
	 * Always disabled
	 * @return boolean
	 */
	public function isActive() {
		return false;
	}
	/**
	 * Trackable
	 * @return boolean
	 */
	public function isTrackingAvailable(){
        return true; 
    }
    
}