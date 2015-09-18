<?php

/**
 * Class GH_AttributeRules_Model_Observer
 */
class GH_AttributeRules_Model_Observer
{
    /**
     * @param  Varien_Event_Observer $observer
     */
    public function saveProductAttributeRule($observer)
    {
        $saveAsRule = $observer->getSaveAsRule();
        $attributeMode = $observer->getAttributeMode();
        $restQuery = $observer->getRestQuery();
        $attributeValue = $observer->getAttributeValue();

        $attributeCode = $observer->getAttributeCode();

        /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        $attribute = $this->_getAttribute($attributeCode);
        $frontendInput = $attribute->getFrontendInput();

        if (!$saveAsRule ||
            //IF multiselect SET rules only for "add" and "set" mode (Do NOT save for "sub" mode)
            ($frontendInput == 'multiselect' && !in_array($attributeMode, array("add", "set")))
            ||
            //Do not save empty value if mode "add"
            (empty($attributeValue) && $frontendInput == 'multiselect' && in_array($attributeMode, array("add")))
        ) {
            return;
        }

        $storeId = $observer->getStoreId();
        $vendorId = $observer->getVendorId();


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
        if ($frontendInput == 'multiselect' && $attributeMode == "add") {
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
}