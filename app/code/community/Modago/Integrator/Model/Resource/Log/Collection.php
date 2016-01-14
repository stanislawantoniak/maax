<?php

/**
 * Collection for logs from Modago Api
 *
 * Class Modago_Integrator_Model_Resource_Log_Collection
 */
class Modago_Integrator_Model_Resource_Log_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct() {
		parent::_construct();
		$this->_init('modagointegrator/log');
	}
}
 
