<?php
class Zolago_Rma_Model_Resource_ReturnReason_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{

    protected function _construct() {
        parent::_construct();
        $this->_init('zolagorma/returnreason');
    }
}