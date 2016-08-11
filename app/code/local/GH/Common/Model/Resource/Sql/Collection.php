<?php

/**
 * Class GH_Common_Model_Resource_Sql_Collection
 */
class GH_Common_Model_Resource_Sql_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('ghcommon/sql');
    }

}