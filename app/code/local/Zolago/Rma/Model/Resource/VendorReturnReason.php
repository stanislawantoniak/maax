<?php
class Zolago_Rma_Model_Resource_VendorReturnReason extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/vendorreturnreason', "vendor_return_reason_id");
    }

}