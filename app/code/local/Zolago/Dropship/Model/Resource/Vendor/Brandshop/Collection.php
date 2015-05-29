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
}