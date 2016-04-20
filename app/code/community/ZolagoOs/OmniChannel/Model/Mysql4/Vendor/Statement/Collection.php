<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Statement_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_statement');
        parent::_construct();
    }

    public function addVendorFilter($vendorIds)
    {
        $this->getSelect()->where('vendor_id in (?)', (array)$vendorIds);
        return $this;
    }
}