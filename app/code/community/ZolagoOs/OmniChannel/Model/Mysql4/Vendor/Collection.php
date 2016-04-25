<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor');
        parent::_construct();
    }

    public function addVendorFilter($vendorIds)
    {
        $this->getSelect()->where('vendor_id in (?)', (array)$vendorIds);
        return $this;
    }

    public function addProductFilter($productIds, $priority=null)
    {
        $this->getSelect()->join(
            array('product'=>$this->getTable('vendor_product')),
            'product.vendor_id=main_table.vendor_id',
            array('product_id', 'priority')
        )->where('product.product_id in (?)', (array)$productIds);

        if (!is_null($priority)) {
            //$this->getSelect()->where('product.priority=?', $priority);
        }

        return $this;
    }

    public function addStatusFilter($status)
    {
        $this->getSelect()->where('status in (?)', $status);
        return $this;
    }
}