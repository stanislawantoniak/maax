<?php
/**
 * shipping object interface
 */

interface Orba_Shipping_Model_Carrier_Interface extends Mage_Shipping_Model_Carrier_Interface {
    
    public function prepareSettings($params,$shipment,$udpo);

    public function prepareRmaSettings($params,$vendor,$rma);
    
    public function setSenderAddress($address);
    
    public function setReceiverAddress($address);
    
    public function setReceiverCustomerAddress($data);
    
    public function createShipments();
    
    public function createShipmentAtOnce();
    
    public function processTrack($track,$data);
    
    public function cancelTrack($track);

    public function setShipped();
    
    public function getAggregatedPdfObject();
    
    // modal for vendor panel
    public function getShippingModal();
    // allow print delivery letter
    public function isLetterable();
    // link to get delivery letter
    public function getLetterUrl();
}