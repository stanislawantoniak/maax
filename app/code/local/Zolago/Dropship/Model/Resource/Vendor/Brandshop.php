<?php
/**
 * resource for vendor brandshop
 */
class Zolago_Dropship_Model_Resource_Vendor_Brandshop extends Mage_Core_Model_Resource_Db_Abstract {
    protected function _construct()
    {
        $this->_init('zolagodropship/vendor_brandshop', "vendor_brandshop_id");
    }

}
