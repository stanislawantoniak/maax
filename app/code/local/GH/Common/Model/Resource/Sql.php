<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Kind
 */
class GH_Common_Model_Resource_Sql extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghcommon/sql', 'id');
    }

}