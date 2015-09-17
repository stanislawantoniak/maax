<?php
/**
 * Class GH_AttributeRules_Block_Attribute
 */
class GH_AttributeRules_Block_Attribute extends Mage_Core_Block_Template
{
    /**
     * @return GH_AttributeRules_Model_Resource_AttributeRule_Collection
     */
    public function getRules()
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

        return $collection;
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

}