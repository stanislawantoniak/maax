<?php
/**
 * Class GH_AttributeRules_Block_Attribute
 */
class GH_AttributeRules_Block_Attribute extends Mage_Core_Block_Template
{
    /**
     * @return GH_AttributeRules_Model_Resource_AttributeRule_Collection
     */
    public function getRulesData()
    {
        /** @var GH_AttributeRules_Model_Resource_AttributeRule_Collection $collection */
        $collection = Mage::getResourceModel("gh_attributerules/attributeRule_collection");
        $collection->addVendorFilter($this->getVendor());

        /* @var $gridModel Zolago_Catalog_Model_Vendor_Product_Grid */
        $gridModel = Mage::getModel("zolagocatalog/vendor_product_grid");

        /** @var GH_AttributeRules_Model_AttributeRule $rule */
        foreach ($collection as $rule) {
            $rule->setData("attribute", $gridModel->getAttribute($rule->getColumn()));
        }

        $data = array();

        foreach ($collection as $rule) {
            $col = $rule->getColumn();
            $value = $rule->getValue();
            $data[$col]['attribute'] = $rule->getData("attribute");
            $data[$col]['value'][$value][] = $rule;
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl("*/*/*");
    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor()
    {
        return Mage::getModel("udropship/session")->getVendor();
    }

    /**
     * @param GH_AttributeRules_Model_AttributeRule $rule
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getFilterAsText($rule) {
        /** @var GH_AttributeRules_Helper_Data $helper */
        $helper = Mage::helper("gh_attributerules");

        $filter = unserialize($rule->getFilter());
        if ($filter !== false) {

            /** @var $gridModel Zolago_Catalog_Model_Vendor_Product_Grid */
            $gridModel = Mage::getModel("zolagocatalog/vendor_product_grid");



            $store = $this->getLabelStore();
            $str = '';
            foreach ($filter as $typeKey => $item) {
                foreach ($item as $key => $value) {
                    /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
                    $attr = $gridModel->getAttribute($key);
                    $str .= $attr->getStoreLabel($store) . $helper->__(" equal ");
                    if ($typeKey == 'regular') {
                        $str .= $attr->getSource()->getOptionText($value);
                    } else {
                        $str .= $value;
                    }
                    $str .= $helper->__(" and ");
                }
            }

            return substr($str, 0, -strlen($helper->__(" and ")));
        } else {
            return $helper->__("All products in current category");
        }
    }

    /**
     * @return Mage_Core_Model_Store
     */
    public function getLabelStore() {
        return $this->getVendor()->getLabelStore();
    }
}