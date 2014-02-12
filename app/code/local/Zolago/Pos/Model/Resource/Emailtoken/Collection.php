<?php

class Zolago_Pos_Model_Resource_Pos_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagopos/pos');
    }
    
}
