<?php
class Zolago_Solrsearch_Model_Resource_Queue_Item_Collection 
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	
	protected function _construct() {
        parent::_construct();
        $this->_init('zolagosolrsearch/queue_item');
    }
	
}