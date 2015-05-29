<?php

/**
 * definition of wsdl api
 */

class GH_Api_Block_WsdlTest extends Mage_Core_Block_Template
{
    
    public function getTargetNamespace() {
        return $this->getUrl('ghapi/ws/test');
    }
    public function getTns() {
        return $this->getUrl('ghapi/ws/test');        
    }
    public function getWsUrl() {
        return $this->getUrl('ghapi/ws/test');
    }

}
