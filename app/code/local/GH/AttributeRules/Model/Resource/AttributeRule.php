<?php

/**
 * Class GH_AttributeRules_Model_Resource_AttributeRule
 */
class GH_AttributeRules_Model_Resource_AttributeRule extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gh_attributerules/gh_attribute_rules', 'attribute_rule_id');
    }

}