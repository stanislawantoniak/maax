<?php

/**
 * Class GH_AttributeRules_Model_Rule
 *
 * @method string getAttributeRuleId()
 * @method string getVendorId()
 * @method string getFilter()
 * @method string getColumn()
 * @method string getValue()
 *
 */
class GH_AttributeRules_Model_Rule extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('gh_attributerules/attribute_rules');
    }
}