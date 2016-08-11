<?php
/**
 * collection for vendor brandshop settings
 */
class Zolago_Dropship_Model_Resource_Vendor_Brandshop_Collection 
    extends  Mage_Core_Model_Resource_Db_Collection_Abstract {
    
    protected function _construct() {
        $this->_init('zolagodropship/vendor_brandshop');
        parent::_construct();
    }
    
    public function setCanAddFilter($vendorId) {
        $this->addFilter('main_table.vendor_id',$vendorId);
        $name = Mage::getSingleton('core/resource')->getTableName('udropship/vendor');
        $this->getSelect()->join(
            array( 
                'vendor' => $name,
            ),
            'main_table.brandshop_id = vendor.vendor_id',
            array( 
                'vendor_name' => 'vendor_name',
            
            )
        )
        -> where(
            'main_table.can_add_product = TRUE'
        );            
    }
}