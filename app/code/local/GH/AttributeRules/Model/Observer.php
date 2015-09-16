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

        $attributeValue = $observer->getAttributeValue();

        if (!$saveAsRule || !in_array($attributeMode, array("set", ""))) {
            return;
        }

        $storeId = $observer->getStoreId();
        $vendorId = $observer->getVendorId();
        $attributeCode = $observer->getAttributeCode();
        $attributeId = Mage::getResourceModel('eav/entity_attribute')
            ->getIdByCode('catalog_product', $attributeCode);


        $data = array(
            "vendor_id" => $vendorId,
            //"filter" => '',
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
}