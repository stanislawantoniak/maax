<?php
class Zolago_Holidays_Model_Resource_Holiday_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	
	protected function _construct() {
        parent::_construct();
        $this->_init('zolagoholidays/holiday');
    }
}
