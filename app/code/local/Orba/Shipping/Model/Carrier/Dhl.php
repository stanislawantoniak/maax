<?php
/**
 * Dhl carrier 
 */
class Orba_Shipping_Model_Carrier_Dhl extends Orba_Shipping_Model_Carrier_Abstract {
    			
	const CODE = "orbadhl";
    protected $_code = self::CODE;

    public function prepareSettings($params,$shipment,$udpo) {
        $pos = $udpo->getDefaultPos();
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
        $settings = Mage::helper('udpo')->getDhlSettings($pos->getId(),$vendor->getId());
        
        $weight =  $params->getParam("weight");

        if(empty($weight)) {
            if($shipment && $shipment->getTotalWeight()) {
                $weight = ceil($shipment->getTotalWeight());
            } else {
                $weight = Mage::helper('orbashipping/carrier_dhl')->getDhlDefaultWeight();
            }
        }


        $shipment->setTotalWeight($weight);
        $order = $shipment->getOrder();
        if ($order->getPayment()->getMethod() == 'cashondelivery') {
            $deliveryValue = $udpo->getGrandTotalInclTax()-$udpo->getPaymentAmount();
        } else {
            $deliveryValue = 0;
        }
        $shipmentSettings = array(
                                    'type'			=> $params->getParam('specify_orbadhl_type'),
                                    'width'			=> $params->getParam('specify_orbadhl_width'),
                                    'height'		=> $params->getParam('specify_orbadhl_height'),
                                    'length'		=> $params->getParam('specify_orbadhl_length'),
                                    'weight'		=> $weight,
                                    'quantity'		=> Orba_Shipping_Model_Carrier_Client_Dhl::SHIPMENT_QTY,
                                    'nonStandard'	=> $params->getParam('specify_orbadhl_custom_dim'),
                                    'shipmentDate'  => $params->getParam('specify_orbadhl_shipping_date'),
                                    'shippingAmount'=> $params->getParam('shipping_amount'),
                                    'deliveryValue' => ($deliveryValue>0)? $deliveryValue:0,
                                    'content'		=> Mage::helper('zolagopo')->__('Shipment') . ': ' . $shipment->getIncrementId(),
                                );
        // add shipment settings
        foreach ($shipmentSettings as $key => $val) {
            $settings[$key] = $val;
        }
        $this->setShipmentSettings($settings);
        return $settings;

    }
    public function setReceiverCustomerAddress($data) {
        $params = array (
            'country' => $data['country_id'],
            'name' => $data['firstname'].' '.$data['lastname'].($data['company'] ? ' '.$data['company'] : ''),
            'postcode' =>$data['postcode'],
            'city' => $data['city'],
            'street' => $data['street'],
            'contact_person' => $data['firstname'].' '.$data['lastname'],
            'contact_phone' => $data['telephone'], 
            'contact_email' => $data['email'],
        );
        $this->setReceiverAddress($params);
    }
    protected function _startClient() {
        $settings = $this->_settings;
        $client = Mage::helper('orbashipping/carrier_dhl')->startClient($settings);
        if (!$client) {
            throw new Mage_Core_Exception(Mage::helper('orbashipping')->_('Cant connect to %s server','DHL'));
        }
        $client->setShipmentSettings($settings);
        $client->setShipperAddress($this->_senderAddress);
        $client->setReceiverAddress($this->_receiverAddress);
        return $client;
    }
    public function createShipments() {
        $client = $this->_startClient();
        $dhlResult = $client->createShipments();
        $results = $client->processDhlShipmentsResult('createShipments',$dhlResult);
        return $results;
    }
    public function createShipmentAtOnce() {
        $client = $this->_startClient();
        // only for rma requests
//       $client->setParam('shippingPaymentType',Orba_Shipping_Model_Carrier_Client_Dhl::PAYER_TYPE_RECEIVER);
        return $client->createShipmentAtOnce();                
    }
	                	    
}