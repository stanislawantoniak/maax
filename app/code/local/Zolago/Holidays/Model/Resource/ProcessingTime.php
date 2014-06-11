<?php
class Zolago_Holidays_Model_Resource_ProcessingTime extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected function _construct() {
		$this->_init('zolagoholidays/processingtime', "processingtime_id");
	}
	
}
