<?php

class ZolagoOs_IAIShop_Block_Settings extends Mage_Core_Block_Template
{
    /**
     * @return ZolagoOs_OmniChannel_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('udropship/session');
    }

    public function getFormAction() {
        return $this->getUrl('iaishop/settings/save', array("_secure" => true));
    }
}