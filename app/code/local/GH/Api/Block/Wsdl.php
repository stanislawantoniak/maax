<?php

/**
 * definition of wsdl api
 */

class GH_Api_Block_Wsdl extends Mage_Core_Block_Template
{
    
    public function getTargetNamespace() {
        return $this->getUrl('ghapi/ws');
    }
    public function getTns() {
        return $this->getUrl('ghapi/ws');        
    }
    public function getWsUrl() {
        return $this->getUrl('ghapi/ws');
    }

}
