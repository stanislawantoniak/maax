<?php
/**
 * soap server
 */
class GH_Api_WsController extends Mage_Core_Controller_Front_Action {
    
    
    /**
     * preparing soap server (test or production)
     * 
     * @param string $wsdl wsdl url
     * @param string $class api soap class
     * @return 
     */

    protected function _prepareServer($wsdl,$class) {
        $testFlag = (bool)Mage::getConfig()->getNode('global/test_server');  
        $helper = Mage::helper('ghapi');
        $params = array ('encoding' => 'UTF-8', 'soap_version' => SOAP_1_2);                                    
        if ($testFlag) {  
            $url = $helper->prepareWsdlUri($wsdl,$params);
            $params['trace'] = 1;
        } else {
            $url = $wsdl;
        }
        $server = new SoapServer($url, $params);            
        if ($testFlag) {
            @unlink($url);
        }
        $server->setClass(get_class(Mage::getModel($class)));
        $server->handle();
    }
    public function indexAction() {    
        $this->_prepareServer($this->_getWsdlUrl(),'ghapi/soap');
    }
    public function testAction() {    
        $this->_prepareServer(Mage::helper('ghapi')->getWsdlTestUrl(),'ghapi/soap_test');
    }
    protected function _getWsdlUrl() {
        $uri =  Mage::helper('ghapi')->getWsdlUrl();
        return $uri;
    }
}