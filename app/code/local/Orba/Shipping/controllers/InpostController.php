<?php
/**
 * inpost controller
 */
class Orba_Shipping_InpostController extends Orba_Shipping_Controller_Lp
{	

    protected function _getHelper() {
        return Mage::helper('orbashipping/packstation_inpost');
    }
    protected function _getCode() {
        return Orba_Shipping_Model_Packstation_Inpost::CODE;
    }
    protected function _getCarrierCode() {
        return 'INPOST';
    }
    protected function _getLpDownloadUrl() {
        return 'orbashipping/inpost/lpDownload';
    }
    protected function _getSettings() {
        $request = $this->getRequest();
        $vendorId   = $request->getParam('vId');
        $posId      = $request->getParam('posId');
        $vendorModel    = Mage::getModel('udropship/vendor')->load($vendorId);
        $posModel    = Mage::getModel('zolagopos/pos')->load($posId);
                
        return Mage::helper('ghinpost')->getApiSettings($vendorModel, $posModel);        
    }

}
