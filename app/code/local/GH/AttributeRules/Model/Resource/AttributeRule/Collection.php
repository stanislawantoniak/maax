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
     * @param Unirgy_Dropship_Model_Vendor | ini $vendor
     * @return Zolago_Banner_Model_Resource_Banner_Collection
     */
    public function addVendorFilter($vendor) {
        if($vendor instanceof Unirgy_Dropship_Model_Vendor){
            $vendor = $vendor->getId();
        }
        $this->addFieldToFilter('vendor_id',(int)$vendor);
        return $this;
    }
}