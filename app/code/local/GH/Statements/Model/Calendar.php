<?php
/**
 *   Calendar for statements
 */
class GH_Statements_Model_Calendar extends Mage_Core_Model_Abstract {
    protected function _construct() {
        $this->_init('ghstatements/calendar');
        parent::_construct();
    }
}

