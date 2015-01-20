<?php
class Zolago_SalesRule_Model_Resource_Relation_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

    protected function _construct()
    {
        parent::_construct();
        $this->_init('zolagosalesrule/relation');
    }
	
}