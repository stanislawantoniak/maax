<?php
/**
 * vendor brand permission collection
 */
class Zolago_Sizetable_Model_Resource_Vendor_Brand_Collection 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
    
    
        protected function _construct() {
            parent::_construct();
            $this->_init('zolagosizetable/vendor_brand');
        }
                            
}