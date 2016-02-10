<?php

/**
 * Class GH_Marketing_Model_Resource_Marketing_Budget_Collection
 */
class GH_Marketing_Model_Resource_Marketing_Budget_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('ghmarketing/marketing_budget');
    }
}
 
