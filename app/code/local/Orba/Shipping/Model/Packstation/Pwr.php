<?php

/**
 * Class Orba_Shipping_Model_Packstation_Pwr
 */
class Orba_Shipping_Model_Packstation_Pwr extends Orba_Shipping_Model_Carrier_Abstract {

    const CODE = "zospwr";
    protected $_code = self::CODE;
    protected $_client;

    public function isActive() {
        return Mage::helper('zolagoos/pwr')->isActive();
    }
    
    public function prepareSettings($params,$shipment,$udpo) {
        $settings = array (
            'udpo'	=> $udpo,
        );
        $this->setShipmentSettings($settings);
    }
    
    
    protected function _startClient() {
        $client = Mage::getModel('orbashipping/packstation_client_pwr');
        $helper = Mage::helper('zolagoos/pwr');
        $login  = $helper->getPartnerId();
        $password = $helper->getPartnerKey();
        $client->setAuth($login,$password);
        return $client;
    }
    
    public function createShipments() {
        try {
            $client = $this->getClient();  
        } catch (Exception $xt) {
            Mage::logException($xt);
            $message = $xt->getMessage();
            $code = 0;            
        }        
        $result = array(
            'shipmentId' => $code,
            'message' => $message,
        );
        return $result;
    }
    
    public function getShippingModal() {
        return Mage::app()->getLayout()->createBlock('zolagopo/vendor_po_edit_shipping_pwr'); 
    }
    
}