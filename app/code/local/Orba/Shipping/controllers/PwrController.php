<?php
/**
 * Paczka w Ruchu controller
 */
class Orba_Shipping_PwrController extends Orba_Shipping_Controller_Lp
{	
    
    protected function _getCode() {
        return Orba_Shipping_Model_Packstation_Pwr::CODE;
    }

    protected function _getHelper() {
        return Mage::helper('orbashipping/packstation_pwr');
    }
    
    protected function _getCarrierCode() {
        return 'Poczta w Ruchu';
    }
    protected function _getLpDownloadUrl() {
        return 'orbashipping/pwr/lpDownload';
    }
    protected function _getSettings() {
        return array(); // no settings required
    }

}
