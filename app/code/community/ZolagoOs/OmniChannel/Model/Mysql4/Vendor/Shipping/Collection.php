<?php
/**
  
 */

class ZolagoOs_OmniChannel_Model_Mysql4_Vendor_Shipping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('udropship/vendor_shipping');
        parent::_construct();
    }

    public function addVendorFilter($vendorId)
    {
        $this->getSelect()->where('vendor_id=?', $vendorId);
        return $this;
    }

    public function joinShipping()
    {
        $this->getSelect()->join(
            array('shipping'=>$this->getTable('shipping')),
            'shipping.shipping_id=main_table.shipping_id',
            array('shipping_code', 'shipping_title')
        );
        return $this;
    }
}