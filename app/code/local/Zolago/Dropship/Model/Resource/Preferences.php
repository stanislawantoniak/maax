<?php
class Zolago_Dropship_Model_Resource_Preferences extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('zolagodropship/preferences', 'vendor_preferences_id');
    }
}
