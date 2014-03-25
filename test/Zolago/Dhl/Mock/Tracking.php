<?php
class Zolago_Dhl_Mock_Tracking {
    protected $_number;
    public function __construct($number = null) {
        if ($number) {
            $this->_number = $number;
        }
    }
    public function getNumber() {
        return $this->_number;
    }
    public function getCarrierCode() {
        return Zolago_Dhl_Helper_Data::DHL_CARRIER_CODE;
    }
    
}
?>