<?php

/**
 * resource for calendar
 */

class GH_Statements_Model_Resource_Calendar extends Mage_Core_Model_Resource_Db_Abstract {
    
    
    protected function _construct() {
        $this->_init('ghstatements/calendar','calendar_id');
    }
}