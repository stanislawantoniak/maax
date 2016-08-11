<?php

class GH_Inpost_Model_Resource_Locker extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('ghinpost/locker', 'id');
	}
}