<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Product_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_product');
        parent::_construct();
    }

    public function addProductFilter($productIds, $priority=null)
    {
        $this->getSelect()->where('product_id in (?)', (array)$productIds);
        if (!is_null($priority)) {
            //$this->getSelect()->where('priority=?', $priority);
        }
        return $this;
    }

    public function addVendorFilter($vendorIds)
    {
        $this->getSelect()->where('vendor_id in (?)', (array)$vendorIds);
        return $this;
    }
}