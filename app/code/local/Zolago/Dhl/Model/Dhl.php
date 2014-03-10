<?php
/**
 * dhl 
 */
class Zolago_Dhl_Model_Dhl extends Mage_Core_Model_Abstract {
    protected $_auth;
    
    public function setAuth($user,$password) {
        $auth = new StdClass();
        $auth->username = $user;
        $auth->password = $password;
        $this->_auth = $auth;
    }
    
    /**
     * message via soap
     */
    protected function _sendMessage($method,$message = null) {
        $soap = new SoapClient(Mage::getStoreConfig('zolagodhl/wsdl_path'),true);
        $result = $soap->$method($message);
        return $result;
    }
    /**
     * shipments list
     */
     public function getMyShipments($from,$to,$offset = 0) {
         $message = new StdClass();
         $message->authData = $this->_auth;
         $message->createdFrom = $from;
         $message->createdTo = $to;
         $message->offset = $offset;
         $return = $this->_sendMessage('getMyShipments',$message);
         return $return;
     }
}