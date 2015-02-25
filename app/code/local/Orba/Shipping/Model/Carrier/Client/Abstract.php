<?php
/**
 * abstract carrier client
 */
class Orba_Shipping_Model_Carrier_Client_Abstract extends Mage_Core_Model_Abstract {
    protected $_auth;
    protected $_shipperAddress;
    protected $_receiverAddress;
    protected $_operator;
    protected $_settings;
    protected $_default_params = array();
    /**
     *
     * authorization data
     * @param string $user
     * @param string $password
     * @param string $account
     * @return
     */

    public function setAuth($user,$password,$account = null) {
        $auth = new StdClass();
        $auth->username = $user;
        $auth->password = $password;
        $auth->account = $account;
        $this->_auth = $auth;
    }
    
    /**
     * prepare soap header
     */
    protected function _prepareSoapHeader() {
        return null;
    }
    
    /**
     * prepare error message
     * @param Exception $xt
     */
    protected function _prepareErrorMessage($xt) {
            $result = array(
                'error' => $xt->getMessage()
            );
            return $result;
    }
    /**
     * message via soap
     */
    protected function _sendMessage($method, $message = null)
    {
	    try {
            $wsdl = $this->_getWsdlUrl();
            $mode = $this->_getSoapMode();
            $soap = new SoapClient($wsdl, $mode);
            $header = $this->_prepareSoapHeader();
            if ($header) {
                $soap->__setSoapHeaders($header);
            }
            $result = $soap->$method($message);
        } catch (Exception $xt) {
            $result = $this->_prepareErrorMessage($xt);
        }

        return $result;
    }
    
    protected function _getWsdlUrl() {
        return null;
    }
    protected function _getSoapMode() {
        return array();
    }
    public function setShipperAddress($address) {
        $this->_shipperAddress = $address;
    }
    public function setReceiverAddress($address) {
        $this->_receiverAddress = $address;
    }
    /**
     * @param Zolago_Operator_Model_Operator $operator
     */
    public function setOperator($operator) {
        if (!empty($operator)) {
            $this->_operator = $operator;
        }
    }
    public function setShipmentSettings($params) {
        $this->_settings = $params;
    }
    
    public function setParam($param,$value) {
        if (!isset($this->_default_params[$param])) {
            Mage::throwException(sprintf('Wrong param name: %s',$param));
        }
        $this->_default_params[$param] = $value;
    }


}