<?php

/**
 * Marketing cost type model resource
 */
class GH_Marketing_Model_Resource_Marketing_Cost_Type extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('ghmarketing/marketing_cost_type', "marketing_cost_type_id");
    }
}