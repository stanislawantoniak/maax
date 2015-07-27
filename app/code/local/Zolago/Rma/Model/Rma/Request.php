<?php
/**
 * dhl request for rma
 */
class Zolago_Rma_Model_Rma_Request extends Mage_Core_Model_Abstract {
    protected $_rma;
    protected $_params = array();
    
    public function setParam($key,$val) {
        $this->_params[$key] = $val;
    }
    public function setRma($rma) {
        $this->_rma = $rma;
    }
    
    /**
     * prepare default dhl settings
     * @return array
     */
    protected function _prepareDhlSettings() {
        $vendorId = $this->_rma->getUdropshipVendor();
        return Mage::helper('orbashipping/carrier_dhl')->getDhlRmaSettings($vendorId);
    }
    public function prepareRequest($rma = null) {
        if ($rma) {
            $this->setRma($rma);
        }
        if (!$dhlSettings = $this->_prepareDhlSettings()) {
            return false;
        }
        
        $carrierManager = Mage::helper('orbashipping')->getShippingManager(Orba_Shipping_Model_Carrier_Dhl::CODE);
        
        foreach ($this->_params as $key=>$param) {
            $dhlSettings[$key] = $param;
        }
        $dhlSettings['deliveryValue'] = (string)$rma->getTotalValue();
        $carrierManager->setShipmentSettings($dhlSettings);        
        $vendorId = $rma->getUdropshipVendor();
        $vendor = Mage::getModel('udropship/vendor')->load($vendorId);
        // sender customer, receiver vendor
        $address = $vendor->getRmaAddress();
        $carrierManager->setReceiverAddress($address);
        
        $address = $rma->getFormattedAddressForCustomer();
        $carrierManager->setSenderAddress($address);

        return $carrierManager->createShipmentAtOnce();
    }
}