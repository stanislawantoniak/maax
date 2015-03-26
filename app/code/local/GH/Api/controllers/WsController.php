<?php
/**
 * soap server
 */
class GH_Api_WsController extends Mage_Core_Controller_Front_Action {
    
    public function indexAction() {    
        $server = new SoapServer($this->_getWsdlUrl(), array('encoding' => 'UTF-8'));
        $server->setClass(get_class(Mage::getModel('ghapi/soap')));
        $server->handle();
    }
    protected function _getWsdlUrl() {
        $uri =  Mage::helper('ghapi')->getWsdlUrl();
        return $uri;
    }
}