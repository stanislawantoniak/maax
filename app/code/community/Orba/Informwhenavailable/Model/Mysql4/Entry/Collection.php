<?php
class Orba_Informwhenavailable_Model_Mysql4_Entry_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {
    
    protected function _construct(){
        parent::_construct();
        $this->_init('informwhenavailable/entry');
    }
    
}
