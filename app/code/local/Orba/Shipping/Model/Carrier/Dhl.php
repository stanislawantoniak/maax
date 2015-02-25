<?php
/**
 * Dhl carrier 
 */
class Orba_Shipping_Model_Carrier_Dhl extends Orba_Shipping_Model_Carrier_Abstract {
    			
	const CODE = "orbadhl";
    protected $_code = self::CODE;
    protected $_client;
    protected $_senderAddress;
    protected $_recevierAddress;

    public function prepareParams($params,$shipment,$udpo) {
        $pos = $udpo->getDefaultPos();
        $vendor = Mage::helper('udropship')->getVendor($udpo->getUdropshipVendor());
        $settings = Mage::helper('udpo')->getDhlSettings($pos->getId(),$vendor->getId());
        $this->_client = Mage::helper('orbashipping/carrier_dhl')->startClient($settings);
        
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
        $this->_client->setShipmentSettings($shipmentSettings);

    }
    public function setSenderAddress($address) {
        $this->_client->setShipperAddress($address);
    }
    public function setReceiverAddress($address) {
        $this->_client->setReceiverAddress($address);
    }
    public function setReceiverCustomerAddress($data) {
        $params = array (
            'name' => $data['firstname'].' '.$data['lastname'].($data['company'] ? ' '.$data['company'] : ''),
            'postcode' =>$data['postcode'],
            'city' => $data['city'],
            'street' => $data['street'],
            'contact_person' => $data['firstname'].' '.$data['lastname'],
            'contact_phone' => $data['telephone'], 
            'contact_email' => $data['email'],
        );
        $this->_cilent->setReceiverAddress($params);
    }
    public function createShipments() {
        $dhlResult = $this->_client->createShipments();
        $results = $this->_client->processDhlShipmentResults('createShipments',$dhlResults);
        return $results;
    }
	                	    
}