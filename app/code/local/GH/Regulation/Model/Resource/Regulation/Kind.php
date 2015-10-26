<?php

/**
 * Class GH_Regulation_Model_Resource_Regulation_Kind
 */
class GH_Regulation_Model_Resource_Regulation_Kind extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('ghregulation/regulation_kind', 'regulation_kind_id');
    }

}