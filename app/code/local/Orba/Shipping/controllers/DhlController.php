<?php
/**
 * dhl controller
 */
class Orba_Shipping_DhlController extends Orba_Shipping_Controller_Lp
{	

    protected function _getHelper() {
        return Mage::helper('orbashipping/carrier_dhl');
    }
    
    protected function _getCarrierCode() {
        return 'DHL';
    }
    protected function _getLpDownloadUrl() {
        return 'orbashipping/dhl/lpDownload';
    }
    protected function _getSettings() {
        $request = $this->getRequest();
        $vendorId   = $request->getParam('vId');
        $posId      = $request->getParam('posId');
        $vendorModel    = Mage::getModel('udropship/vendor')->load($vendorId);
                
        return Mage::helper('udpo')->getDhlSettings($vendorModel, $posId);        
    }

    public function checkDhlZipAction()
    {
        $zip = Mage::app()->getRequest()->getParam('postcode');
        $country = Mage::app()->getRequest()->getParam('country');
        $response = array();
        if (empty($zip)) {
            $response['status'] = 'error';
            $response['message'] = 'Please enter zip code';
            echo json_encode($response);
            return;
        }

        $dhlHelper = Mage::helper('orbashipping/carrier_dhl');
        $dhlValidZip = $dhlHelper->isDHLValidZip($country,$zip);

        if (!$dhlValidZip) {
            $response['status'] = 'error';
            $response['message'] = 'Invalid Dhl Postal Code';
            echo json_encode($response);
            return;
        }

        $response['status'] = 'success';
        $response['message'] = 'Zip code is valid';
        echo json_encode($response);
        return;

    }
}