<?php
/**
 * shipping object interface
 */

interface Orba_Shipping_Model_Carrier_Interface extends Mage_Shipping_Model_Carrier_Interface {
    
    public function prepareSettings($params,$shipment,$udpo);
    
    public function setSenderAddress($address);
    
    public function setReceiverAddress($address);
    
    public function setReceiverCustomerAddress($data);
    
    public function createShipments();
}