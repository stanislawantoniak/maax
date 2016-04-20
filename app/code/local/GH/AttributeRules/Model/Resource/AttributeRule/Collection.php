<?php

/**
 * Class GH_AttributeRules_Model_Resource_AttributeRule_Collection
 */
class GH_AttributeRules_Model_Resource_AttributeRule_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('gh_attributerules/attributeRule');
    }

    /**
     * @param ZolagoOs_OmniChannel_Model_Vendor|int $vendor
     * @return Zolago_Banner_Model_Resource_Banner_Collection
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter('main_table.vendor_id',(int)$vendor);
        return $this;
    }

    /**
     * @param array()|int $ids
     * @return $this
     */
    public function addAttributeIdFilter($ids) {
        if (empty($ids)) {
            return $this;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $this->addFieldToFilter("main_table.column", array("in" => array($ids)));
        return $this;
    }

    /**
     * @param array()|int $values
     * @return $this
     */
    public function addValueIdFilter($values) {
        if (empty($values)) {
            return $this;
        }
        if (!is_array($values)) {
            $values = array($values);
        }
        $this->addFieldToFilter("main_table.value", array("in" => array($values)));
        return $this;
    }

    /**
     * @param array()|int $ids
     * @return $this
     */
    public function addRuleIdFilter($ids) {
        if (empty($ids)) {
            return $this;
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        $this->addFieldToFilter("main_table.attribute_rule_id", array("in" => array($ids)));
        return $this;
    }
}