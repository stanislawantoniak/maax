<?php

/**
 * Class GH_Statements_Model_Resource_Vendor_Balance_Collection
 */
class GH_Statements_Model_Resource_Vendor_Balance_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/vendor_balance');
    }

}