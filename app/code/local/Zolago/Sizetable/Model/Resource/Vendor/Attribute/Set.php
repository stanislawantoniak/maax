<?php
class Zolago_Sizetable_Model_Resource_Vendor_Attribute_Set extends Mage_Core_Model_Resource_Db_Abstract {
    
    
    protected function _construct() {
        $this->_init('zolagosizetable/vendor_attribute_set','vendor_attribute_id');
    }
}