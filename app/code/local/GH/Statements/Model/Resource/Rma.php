<?php

/**
 * Class GH_Statements_Model_Resource_Rma
 */
class GH_Statements_Model_Resource_Rma extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/rma', 'id');
    }

}