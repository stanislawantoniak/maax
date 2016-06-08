<?php
/**
 * client poczta polska tracking
 */
class Orba_Shipping_Model_Post_Client_Tracking extends Orba_Shipping_Model_Client_Soap {


    protected $_default_params = array (
                                 );


    /**
     *
     */
    protected function _construct() {
        $this->_init('orbashipping/post_client_tracking');
    }
    
    
     
    /**
     * wsdl url 
     *
     * return string;
     */
    protected function _getWsdlUrl() {
        return Mage::getStoreConfig('carriers/zolagopp/tracking_gateway');
    }
    
    
    /**
     * soap header
     */
    protected function _prepareSoapHeader() {
        $securityType = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";
        $login = Mage::getStoreConfig('carriers/zolagopp/tracking_id');
        $password = Mage::helper('core')->decrypt(Mage::getStoreConfig('carriers/zolagopp/tracking_password'));
        $passType = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText";
        $passwordSoap = new SoapVar($password,XSD_STRING,NULL,$passType,NULL,$securityType);
        $loginSoap = new SoapVar($login,XSD_STRING,NULL,NULL,NULL,$securityType);
        $auth = new StdClass();
        $auth->Username = $loginSoap;
        $auth->Password = $passwordSoap;
        $authType = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd";
        $authSoap = new SoapVar($auth,SOAP_ENC_OBJECT, NULL,$authType,'UsernameToken',$securityType);
        $token = new StdClass();
        $token->UsernameToken = $authSoap;
        $security = new SoapVar($token, SOAP_ENC_OBJECT, NULL, $securityType,'Security',$securityType);
        $header = new SoapHeader($securityType,'Security',$security,true);
        return $header;
        
    }
    /**
     * @return array
     */
    protected function _getSoapMode() {
            $mode = array
                    (
                        'soap_version' => 'SOAP_1_1',  // use soap 1.1 client
                        'trace' => 1,
                    );
            return $mode;
    }
    
    
    
    /**
     *  tracking
     */
     public function getTrackStatus($number) {
         $message = new StdClass();
         $message->numer = $number;
         $result = $this->_sendMessage('SprawdzPrzesylke',$message);
         return $result;
     }
}

