<?php
/**
 * Gls carrier 
 */
class Orba_Shipping_Model_Carrier_Gls extends Orba_Shipping_Model_Carrier_Abstract {
    			
    const CODE = "zolagogls";
    protected $_code = self::CODE;
    protected $_number;

    public function prepareSettings($params,$shipment,$udpo) {
        $this->_number = $params->getParam('tracking_id');
    }	    
    
    public function createShipments() {
        $number = $this->_number;
        if ($number) { 
            $result = array (	
                'shipmentId' => $number,
                'message' => '',
            );
        } else {
            $error = Mage::helper('zolagopo')->__('Empty track number');
            $result = array (	
                'shipmentId' => $number,
                'message' => Mage::helper('zolagopo')->__('%s Service Error: %s','UPS',$error)
            );
        }
        return $result;
    }
    public function createShipmentAtOnce() {
        Mage::throwException(Mage::helper('orbacommon')->__('Not implemented yet'));
    }
}