<?php
/**
 * collection for url table
 */
class GH_Marketing_Model_Resource_Marketing_Cost_Type_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	protected function _construct() {
		parent::_construct();
		$this->_init('ghmarketing/marketing_cost_type');
	}
}
 
