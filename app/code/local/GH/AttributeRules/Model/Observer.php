<?php

/**
 * Class GH_AttributeRules_Model_Observer
 */
class GH_AttributeRules_Model_Observer
{
    /**
     * @see Zolago_Catalog_Vendor_ProductController::saveProductAttributeRule()
     *
     * @event change_product_attribute_after
     * @param Varien_Event_Observer $observer
     */
    public function saveProductAttributeRule($observer)
    {
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = $observer->getVendor();
        $storeId = $observer->getStoreId();
        $restQuery = $observer->getRestQuery();
        $saveAsRule = $observer->getSaveAsRule();
        $attributeCode = $observer->getAttributeCode();
        $attributeMode = $observer->getAttributeMode();
        $attributeValue = $observer->getAttributeValue();

        $vendorId = $vendor->getId();

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $this->_getAttribute($attributeCode);
        $frontendInput = $attribute->getFrontendInput();

        if ($saveAsRule == "false" || !$saveAsRule) {
            return;
        }
        if (!Mage::getSingleton('zolagocatalog/vendor_product_grid')->isAttributeEditable($attribute) || ($attribute->getIsRequired() && trim($attributeValue) == "")) {
            return; // Skip not editable on grid or empty value when required
        }
        if (Mage::helper("zolagocatalog/attribute")->isAttrNotBlockedForMass($attributeCode)) {
            return; // Skip blocked attribute for mass change (like product name)
        }

        if (//IF multiselect SET rules only for "add" and "set" mode (Do NOT save for "sub" mode [~remove mode])
            ($frontendInput == 'multiselect' && !in_array($attributeMode, array("add", "set")))
            ||
            //Do not save empty value if mode "add"
            (empty($attributeValue) && $frontendInput == 'multiselect' && in_array($attributeMode, array("add")))
        ) {
            return;
        }

        $attributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_product', $attributeCode);

        //Prepare filter to save
        $filter = "";
        if (!empty($restQuery)) {
            foreach ($restQuery as $filterItem => $restQueryItem) {
                $id = Mage::getResourceModel('eav/entity_attribute')
                    ->getIdByCode('catalog_product', $filterItem);
                $filter["regular"][$id] = $restQueryItem;
                unset($id);
            }
        }
        $staticFilters = Mage::app()->getRequest()->getParam("static", array());
        if (!empty($staticFilters)) {
            $filter["ext"] = $staticFilters;
        }
        //--Prepare filter to save

        $filterSerialized = !empty($filter) ? serialize($filter) : "";

        // Check if identical rule don't exists
        /** @var GH_AttributeRules_Model_Resource_AttributeRule_Collection $collection */
        $collection = Mage::getResourceModel("gh_attributerules/attributeRule_collection");
        $collection->addVendorFilter($vendor);
        $collection->addAttributeIdFilter($attributeId);

        // Simple hash for skipping
        $hash = $this->_getHash($vendorId, $filterSerialized, $attributeId, $attributeValue);
        /** @var GH_AttributeRules_Model_AttributeRule $rule */
        foreach ($collection as $rule) {
            $ruleHash = $this->_getHash($rule->getVendorId(),$rule->getFilter(), $rule->getColumn(), $rule->getValue());
            if ($hash == $ruleHash) {
                return; // Skip identical
            }
        }
        // --Check if identical rule don't exists

        if ($frontendInput == "multiselect") {
            foreach (explode(",", $attributeValue) as $value) {
                $this->saveRule($vendorId, $filterSerialized, $attributeId, $value);
            }
        } else {
            $this->saveRule($vendorId, $filterSerialized, $attributeId, $attributeValue);
        }

    }


    /**
     * @param $vendorId
     * @param $filterSerialized
     * @param $attributeId
     * @param $attributeValue
     */
    public function saveRule($vendorId, $filterSerialized, $attributeId, $attributeValue)
    {
        $data = array(
            "vendor_id" => $vendorId,
            "filter" => $filterSerialized,
            "column" => $attributeId,
            "value" => $attributeValue
        );

        $attributeRule = Mage::getModel("gh_attributerules/attributeRule");
        $attributeRule->addData($data);

        try {
            $attributeRule->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }


    /**
     * @param Mage_Catalog_Model_Resource_Eav_Attribute | string $attribute
     * @return Mage_Catalog_Model_Resource_Eav_Attribute
     */
    protected function _getAttribute($attribute)
    {
        /* @var $gridModel Zolago_Catalog_Model_Vendor_Product_Grid */
        $gridModel = Mage::getModel("zolagocatalog/vendor_product_grid");
        return $gridModel->getAttribute($attribute);
    }

    protected function _getHash($vendorId, $filter, $column, $value) {
        return $vendorId."_".$filter."_".$column."_".$value;
    }
}