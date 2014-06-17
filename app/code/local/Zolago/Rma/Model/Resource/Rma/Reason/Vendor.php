<?php
class Zolago_Rma_Model_Resource_Rma_Reason_Vendor extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/rma_reason_vendor', "vendor_return_reason_id");
    }

}