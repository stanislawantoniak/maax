<?php

/**
 * Marketing budget model resource
 */
class GH_Marketing_Model_Resource_Marketing_Budget extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('ghmarketing/marketing_budget', "marketing_budget_id");
    }
}