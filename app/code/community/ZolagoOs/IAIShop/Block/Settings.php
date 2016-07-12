<?php

class ZolagoOs_IAIShop_Block_Settings extends Mage_Core_Block_Template{
    protected function _beforeToHtml() {
        $this->getGrid();
        return parent::_beforeToHtml();
    }

    /**
     * @return ZolagoOs_OmniChannel_Model_Session
     */
    protected function _getSession(){
        return Mage::getSingleton('udropship/session');
    }
}