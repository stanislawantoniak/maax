<?php
class Zolago_Rma_Model_Resource_Rma_Reason extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/rma_reason', "return_reason_id");
    }

}