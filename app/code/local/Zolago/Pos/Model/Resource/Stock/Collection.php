<?php

/**
 * Class Zolago_Pos_Model_Resource_Stock_Collection
 */
class Zolago_Pos_Model_Resource_Stock_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct() {
		parent::_construct();
		$this->_init('zolagopos/stock');
	}

}
