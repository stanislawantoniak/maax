<?php
/**
 *   Calendar for statements
 * @method string getName()
 * @method string getCalendarId()
 */
class GH_Statements_Model_Calendar extends GH_Statements_Model_Calendar_Abstract {
    protected function _construct() {
        $this->_init('ghstatements/calendar');
        parent::_construct();
    }
    /**
     * @return GH_Statements_Model_Calendar_Validator
     */
    public function getValidator() {
        return Mage::getSingleton("ghstatements/calendar_validator");
    }

}

