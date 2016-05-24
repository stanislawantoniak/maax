<?php
/**
 * addShipment message
 */
class addShipment {
    public $przesylki; // przesylkaType
    public $idBufor; // int
}
class Orba_Shipping_Model_Post_Message_Pack_List
{
    public function getObject() {
        return new addShipment();
    }
}
