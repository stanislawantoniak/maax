<?php

class Zolago_Operator_Model_Resource_Operator extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagooperator/operator', "operator_id");
	}
	
}

