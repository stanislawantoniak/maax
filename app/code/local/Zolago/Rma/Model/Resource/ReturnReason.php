<?php
class Zolago_Rma_Model_Resource_ReturnReason extends Mage_Core_Model_Resource_Db_Abstract{

    protected function _construct() {
        $this->_init('zolagorma/returnreason', "return_reason_id");
    }

}