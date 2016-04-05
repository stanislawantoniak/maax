<?php

/**
 * Class Zolago_Pos_Model_Resource_Stock
 */
class Zolago_Pos_Model_Resource_Stock extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
		$this->_init('zolagopos/stock', "id");
	}
}