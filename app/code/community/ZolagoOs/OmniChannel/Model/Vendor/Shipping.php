<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Vendor_Shipping extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_shipping');
        parent::_construct();
    }
}