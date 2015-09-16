<?php

/**
 * Class GH_AttributeRules_Model_Observer
 */
class GH_AttributeRules_Model_Observer
{
    public function saveProductAttributeRule($observer)
    {
        $ids = $observer->getProductIds();
        $storeId = $observer->getStoreId();
        $attributeCode = $observer->getAttributeCode();
        foreach($ids as $id){
            Mage::log("Value: product_id: ".$id."-----".Mage::getResourceModel('catalog/product')->getAttributeRawValue($id, $attributeCode, $storeId), null, "YYY.log");

        }
    }
}