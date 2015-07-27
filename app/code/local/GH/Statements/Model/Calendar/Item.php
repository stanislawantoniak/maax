<?php
/**
 *   Calendar for statements
 */
class GH_Statements_Model_Calendar_Item extends GH_Statements_Model_Calendar_Abstract {
    protected function _construct() {
        $this->_init('ghstatements/calendar_item');
        parent::_construct();
    }
    /**
     * @return GH_Statements_Model_Calendar_Item_Validator
     */
    public function getValidator() {
        return Mage::getSingleton("ghstatements/calendar_item_validator");
    }

}

