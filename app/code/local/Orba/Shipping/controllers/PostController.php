<?php
/**
 * poczta polska controller
 */
class Orba_Shipping_PostController extends Orba_Shipping_Controller_Lp
{	

    protected function _getCode() {
        return Orba_Shipping_Model_Post::CODE;
    }
    protected function _getHelper() {
        return Mage::helper('orbashipping/post');
    }
    
    protected function _getCarrierCode() {
        return 'Poczta Polska';
    }
    protected function _getLpDownloadUrl() {
        return 'orbashipping/post/lpDownload';
    }
    protected function _getSettings() {
        return array(); // no settings required
    }

}
