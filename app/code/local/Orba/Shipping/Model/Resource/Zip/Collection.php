<?php
class Orba_Shipping_Model_Resource_Zip_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	
	protected function _construct() {
        parent::_construct();
        $this->_init('orbashipping/zip');
    }
}
