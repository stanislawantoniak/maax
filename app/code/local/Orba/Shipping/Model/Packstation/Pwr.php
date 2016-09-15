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
        $boxSize = Mage::app()->getRequest()->getParam('boxSize');
        $settings = array (
            'udpo'	=> $udpo,
            'destinationCode' => $udpo->getDeliveryPointName(),
            'boxSize' => $boxSize,
        );
        $this->setShipmentSettings($settings);
    }
    
    
    protected function _startClient() {
        $client = Mage::getModel('orbashipping/packstation_client_pwr');
        $helper = Mage::helper('zospwr');
        $login  = $helper->getPartnerId();
        $password = $helper->getPartnerKey();
        $client->setAuth($login,$password);
        return $client;
    }

        
    /**
     * preparing sender address data from po
     */
     protected function _getSenderAddress() {
       $po = $this->_settings['udpo'];
       $pos = $po->getDefaultPos();
       return $pos->getData();
     }
     
    public function createShipments() {
        $code = 0;
        $message = 'No code';
        try {
            $client = $this->getClient();  
            $client->setShipmentSettings($this->_settings);
            $senderAddress = $this->_getSenderAddress();
            $client->setShipperAddress($senderAddress);
            $client->setReceiverAddress($this->_receiverAddress);
            $code = $client->generateLabelBusinessPack();
            $message = 'OK';
        } catch (Exception $xt) {
            Mage::logException($xt);
            $message = $xt->getMessage();
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
    
    protected function _getHelper() {
        return Mage::helper('orbashipping/packstation_pwr');
    }
}