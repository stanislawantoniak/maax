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
        $order = $shipment->getOrder();
        $boxSize = Mage::app()->getRequest()->getParam('boxSize');
        if ($order->getPayment()->getMethod() == 'cashondelivery') {
            $deliveryValue = $udpo->getGrandTotalInclTax()-$udpo->getPaymentAmount();
        } else {
            $deliveryValue = 0;
        }

        $settings = array (
            'udpo'	=> $udpo,
            'destinationCode' => $udpo->getDeliveryPointName(),
            'boxSize' => $boxSize,
            'insurance' => $udpo->getGrandTotalInclTax(),
            'cod'	=> $deliveryValue,
            'orderId'   => $udpo->getIncrementId(),
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
    
    /**
     * allow print letter
     */

    public function isLetterable() {
        return true;
    }
    
    /**
     * path to get letter
     */

    public function getLetterUrl() {
        return 'orbashipping/pwr/lp';
    }
}