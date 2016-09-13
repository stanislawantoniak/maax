<?php
/**
 * Default carrier 
 */
class Orba_Shipping_Model_Carrier_Default extends Orba_Shipping_Model_Carrier_Abstract {
    			
	const CODE = "std";
    protected $_code = self::CODE;
    protected $_number;
    protected $_carrier;
    	              
    public function prepareSettings($params,$shipment,$udpo) {
        $this->_number = $params->getParam('tracking_id');
        $this->_carrier = $params->getParam('carrier_title');
    }  	    
    public function createShipments() {
        $carrier = $this->_carrier;
        $number = $this->_number;
        
        if (!$carrier) {
            $error = Mage::helper('zolagopo')->__('Empty carrier name');
            return array (
                'shipmentId' => '',
                'message' => Mage::helper('zolagopo')->__('Tracking Error: %s',$error)
            );
        }
        if ($number) { 
            $result = array (	
                'shipmentId' => $number,
                'message' => '',
            );
        } else {
            $error = Mage::helper('zolagopo')->__('Empty track number');
            $result = array (	
                'shipmentId' => $number,
                'message' => Mage::helper('zolagopo')->__('%s Service Error: %s',$carrier,$error)
            );
        }
        return $result;
    }
    public function createShipmentAtOnce() {
        Mage::throwException(Mage::helper('orbacommon')->__('Not implemented yet'));
    }

}