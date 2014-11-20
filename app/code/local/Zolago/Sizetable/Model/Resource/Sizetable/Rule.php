<?php

class Zolago_Sizetable_Model_Resource_Sizetable_Rule extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('zolagosizetable/sizetable_rule', 'rule_id');
    }

}