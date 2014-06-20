<?php
class Zolago_Holidays_Model_Resource_Holiday extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected function _construct() {
		$this->_init('zolagoholidays/holiday', "holiday_id");
	}
	
}
