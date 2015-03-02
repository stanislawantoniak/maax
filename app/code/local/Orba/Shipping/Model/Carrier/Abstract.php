<?php
/**
 * abstract model for zolago carriers
 */
class Orba_Shipping_Model_Carrier_Abstract extends
    Mage_Shipping_Model_Carrier_Abstract implements
        Orba_Shipping_Model_Carrier_Interface {

    protected $_senderAddress;
    protected $_recevierAddress;
    protected $_settings;

    public function setShipmentSettings($settings) {
        $this->_settings = $settings;
    }
    public function setSenderAddress($address) {
        $this->_senderAddress = $address;
    }
    public function setReceiverAddress($address) {
        $this->_receiverAddress = $address;
    }

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
    public function prepareSettings($request,$shipment,$udpo) {
        return $this;
    }
    public function prepareRmaSettings($request,$vendor,$rma) {
        return $this;
    }
    
    public function setReceiverCustomerAddress($address) {
        $this->setReceiverAddress($address);
    }
    
    public function createShipments() {
        return null;
    }
    public function createShipmentAtOnce() {
        Mage::throwException(Mage::helper('orbacommon')->__('Not implemented yet'));
    }


}