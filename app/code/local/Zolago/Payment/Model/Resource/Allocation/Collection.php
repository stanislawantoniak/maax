<?php

class Zolago_Payment_Model_Resource_Allocation_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagopayment/allocation');
    }
	
}
