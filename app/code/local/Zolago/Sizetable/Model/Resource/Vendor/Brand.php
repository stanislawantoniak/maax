<?php
class Zolago_Sizetable_Model_Resource_Vendor_Brand extends Mage_Core_Model_Resource_Db_Abstract {
    
    
    protected function _construct() {
        $this->_init('zolagosizetable/vendor_brand','vendor_brand_id');
    }
}