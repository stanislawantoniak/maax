<?php
/**
 * Dhl carrier 
 */
class Zolago_Dhl_Model_Carrier extends 
        Mage_Shipping_Model_Carrier_Abstract implements 
        Mage_Shipping_Model_Carrier_Interface{ 
			
    protected $_code = "zolagodhl";
	
	public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
		$result = Mage::getModel('shipping/rate_result');
		
		foreach($this->getAllowedMethods() as $key=>$method){
			$method = Mage::getModel('shipping/rate_result_method');
			$method->setCarrier($this->_code);
			$method->setCarrierTitle($this->getConfigData('title'));
			$method->setMethod($key);
			$method->setMethodTitle($method);
			$method->setPrice(0);
			$method->setCost(0);
			$result->append($method);

		}

        return $result;
	}
	
	public function getAllowedMethods() {
		return array("method1"=>"DHL");
	}   
	public function isActive() {
		return false;
	}
	public function isTrackingAvailable(){
        return true; /** @todo Impelment */
    }
    
}