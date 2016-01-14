<?php
/**
 * model for zolago carrier
 */
class Modago_Integrator_Model_Shipping_Zolagoshipment extends
    Mage_Shipping_Model_Carrier_Abstract {

	const SHIPPING_METHOD_CODE = 'zolagoshipment';

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
        
}