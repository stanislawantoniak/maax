<?php

/**
 * Class GH_Statements_Model_Resource_Track_Collection
 */
class GH_Statements_Model_Resource_Track_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghstatements/track');
    }

}