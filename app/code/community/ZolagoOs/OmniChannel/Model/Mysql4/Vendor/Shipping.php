<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Shipping extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_shipping', 'vendor_shipping_id');
    }
}