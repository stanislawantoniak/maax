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
    protected $_client;
    const CODE = 'ABSTRACT';

    public function getCode() {	
        return static::CODE;
    }
    public function setShipmentSettings($settings) {
        $this->_settings = $settings;
    }
    public function setSenderAddress($address) {
	    $address['phone'] = $this->getOnlyNumbers($address['phone']);
        $this->_senderAddress = $address;
    }
    public function setReceiverAddress($address) {
	    $address['contact_phone'] = $this->getOnlyNumbers(
            isset($address['contact_phone'])
                ? $address['contact_phone']
                : (isset($address['telephone']) ? $address['telephone'] : ''));
        $this->_receiverAddress = $address;
    }

	protected function getOnlyNumbers($value) {
		return filter_var(str_replace(array('+','-'),'',$value), FILTER_SANITIZE_NUMBER_INT);
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
    public function calculateCharge($track,$rate,$vendor,$packageValue,$isCod) {
        // nothing to do
    }
    
    /**
     * add additional info for track
     */

    public function processTrack($track,$data) {
        return;
    }
    
    /**
     * cancel track 
     */

    public function cancelTrack($track) {
        return;
    }
    
    /**
     * prepare object for generate aggregated pdf file
     */
    public function getAggregatedPdfObject() {
        return null; // if null - there is no aggregated pdf
    }
    
    
    /**
     * actions after shipped po
     */
    public function setShipped() {
        return;
    }
    
    /**
     * set client
     */

    public function setClient($client) {
        $this->_client = $client;
    }
    
    /**
     * get client
     */

    public function getClient() {
        if (!$this->_client) {
            $this->setClient($this->_startClient());
        }
        return $this->_client;
    }
    
    protected function _startClient() {
        return null;
    }
    
    public function getShippingModal() {
        return Mage::app()->getLayout()->createBlock('zolagopo/vendor_po_edit_shipping_empty');
    }
    
}