<?php
/**
 * mock for shipment
 */
class Zolago_Dhl_Mock_Shipment {
    protected $_method;
    public function __construct($method) {
        $this->_method = $method;
    }
    public function getOrder() {
        return $this;
    }
    public function getPayment() {
        return $this;        
    }
    public function getMethod() {
        return $this->_method;
    }
    public function getTotalValue() {
        return 100;
    }
    public function getBaseTaxAmount() {
        return 23;
    }
    public function getShippingAmountIncl() {
        return 10;
    }
    public function getUdpoIncrementId() {
        return 2;
    }
    public function getIncrementId() {
        return 10;
    }
}
?>