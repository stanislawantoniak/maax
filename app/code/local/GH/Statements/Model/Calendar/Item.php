<?php
/**
 *   Calendar for statements
 */
class GH_Statements_Model_Calendar_Item extends Mage_Core_Model_Abstract {
    protected function _construct() {
        $this->_init('ghstatements/calendar_item');
        parent::_construct();
    }
}

