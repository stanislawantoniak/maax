<?php
class Zolago_Holidays_Model_Resource_ProcessingTime_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	
	protected function _construct() {
        parent::_construct();
        $this->_init('zolagoholidays/processingtime');
    }
}
