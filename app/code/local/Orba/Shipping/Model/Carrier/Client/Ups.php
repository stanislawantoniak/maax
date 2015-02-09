<?php
/**
 * client ups
 */
class Orba_Shipping_Model_Carrier_Client_Ups extends Mage_Core_Model_Abstract {
    protected $_auth;


    /**
     *
     * authorization data
     * @param string $user
     * @param string $password
     * @param string $account
     * @return
     */

    public function setAuth($user,$password,$account = null) {
        $auth = array();
        $auth['username'] = $user;
        $auth['password'] = $password;
        $auth['account'] = $account;
        $this->_auth = $auth;
    }

    //{{{
    /**
     * soap header
     * @return
     */
    protected function _createSoapHeader() {
        $usernameToken['Username'] = $this->_auth['username'];
        $usernameToken['Password'] = $this->_auth['password'];
        $serviceAccessLicense['AccessLicenseNumber'] = $this->_auth['account'];
        $upss['UsernameToken'] = $usernameToken;
        $upss['ServiceAccessToken'] = $serviceAccessLicense;

        $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0','UPSSecurity',$upss);
        return $header;

    }
    //}}}
    /**
     * message via soap
     */
    protected function _sendMessage($method, $message = null)
    {
        try {
            $mode = array
                    (
                        'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                        'trace' => 1
                    );

            $wsdl = Mage::getStoreConfig('carriers/orbaups/gateway');
            $soap = new SoapClient($wsdl, $mode);
            $header = $this->_createSoapHeader();
            $soap->__setSoapHeaders($header);
            $result = $soap->$method($message);
        } catch (Exception $xt) {
            $message = !empty($xt->detail->Errors->ErrorDetail->PrimaryErrorCode->Description)? $xt->detail->Errors->ErrorDetail->PrimaryErrorCode->Description:$xt->getMessage();
            $result = array(
                          'error' => $message,
                      );
        }
        return $result;
    }




    /**
     * construct
     */
    protected function _construct() {
        $this->_init('orbashipping/carrier_ups_client');
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

}
