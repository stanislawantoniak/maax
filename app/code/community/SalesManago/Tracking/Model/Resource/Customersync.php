<?php
class SalesManago_Tracking_Model_Resource_Customersync extends Mage_Core_Model_Resource_Db_Abstract {

	protected function _construct() {
        $this->_init('tracking/customersync', 'customer_sync_id');
    }   
}