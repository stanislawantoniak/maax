<?php

/**
 * Class GH_AttributeRules_Model_AttributeRule
 * @method string getAttributeRuleId()
 * @method string getVendorId()
 * @method string getFilter()
 * @method string getColumn()
 * @method string getValue()
 */
class GH_AttributeRules_Model_AttributeRule extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('gh_attributerules/attributeRule');
    }

    /**
     * Return unserialized filter
     *
     * @return mixed
     */
    public function getFilterArray() {
        return unserialize($this->getData("filter"));
    }
}