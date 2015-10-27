<?php

/**
 * Class GH_Statements_Model_Resource_Vendor_Balance
 */
class GH_Statements_Model_Resource_Vendor_Balance extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/vendor_balance', 'balance_id');
    }

}