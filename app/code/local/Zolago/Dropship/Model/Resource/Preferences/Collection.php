<?php
class Zolago_Dropship_Model_Resource_Preferences_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
	
	protected function _construct() {
        parent::_construct();
        $this->_init('zolagodropship/preferences');
    }
}
