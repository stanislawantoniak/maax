<?php
/**
 * client ups
 */
class Orba_Shipping_Model_Carrier_Client_Ups extends Orba_Shipping_Model_Client_Soap {

    /**
     * construct
     */
    protected function _construct() {
        $this->_init('orbashipping/carrier_ups_client');
    }
    //{{{
    /**
     * soap header
     * @return
     */
    protected function _prepareSoapHeader() {
        $usernameToken['Username'] = $this->_auth->username;
        $usernameToken['Password'] = $this->_auth->password;
        $serviceAccessLicense['AccessLicenseNumber'] = $this->_auth->account;
        $upss['UsernameToken'] = $usernameToken;
        $upss['ServiceAccessToken'] = $serviceAccessLicense;

        $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0','UPSSecurity',$upss);
        return $header;

    }
    //}}}
    
    /**
     * wsdl url 
     *
     * return string;
     */
    protected function _getWsdlUrl() {
        return Mage::getStoreConfig('carriers/orbaups/gateway');        
    }
    
    /**
     * @return array
     */
    protected function _getSoapMode() {
            $mode = array
                    (
                        'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                        'trace' => 1
                    );
            return $mode;
    }
    
    
    /**
     * preparing error message
     * @return string
     */
    protected function _prepareErrorMessage($xt) {
        $message = !empty($xt->detail->Errors->ErrorDetail->PrimaryErrorCode->Description)? $xt->detail->Errors->ErrorDetail->PrimaryErrorCode->Description:$xt->getMessage();
        $result = array (
            'error' => $message,
        );
        return $result;
    }


    /**
     * tracking info
     */
    public function getTrackAndTraceInfo($shipmentId) {
        $req['RequestOption'] = '15';
        $tref['CustomerContext'] = 'Add description here';
        $req['TransactionReference'] = $tref;
        $request['Request'] = $req;
        $request['InquiryNumber'] = $shipmentId;
        $request['TrackingOption'] = '02';

        $return = $this->_sendMessage('ProcessTrack',$request);
        return $return;
    }
    
    protected function _getHelper() {
        return Mage::helper('orbashipping/carrier_ups');
    }

}
