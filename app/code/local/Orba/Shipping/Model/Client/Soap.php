<?php
/**
 * abstract soap carrier client
 */
class Orba_Shipping_Model_Client_Soap extends Orba_Shipping_Model_Client_Abstract {
    
    /**
     * prepare soap header
     */
    protected function _prepareSoapHeader() {
        return null;
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
            Mage::logException($xt);
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


}