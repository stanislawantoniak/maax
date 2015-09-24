<?php

class GH_AttributeRules_MassController extends Mage_Core_Controller_Front_Action {

    /**
     * Mass auto fill attributes
     * Note: apply rules to selected
     */
    public function autofillAction() {
        $req        = $this->getRequest();
        $all        = $req->getParam("all", 0);
        $attributes = $req->getParam("attributes", array());
        $values     = $req->getParam("values", array());
        $rules      = $req->getParam("rules", array());
        $storeId    = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $store      = Mage::app()->getStore($storeId);
        $productIds = $req->getParam("product_ids"); // todo
        $attributeSetId = $req->getParam("attribute_set_id", 86); // todo

        if(is_string($productIds)){
            $productIds = explode(",", $productIds);
        }
        if(is_array($productIds) && count($productIds)) {
            $productIds = array_unique($productIds);
        }

        /** @var $gridModel Zolago_Catalog_Model_Vendor_Product_Grid */
        $gridModel = $this->getGridModel();

        // Collecting rules
        /** @var GH_AttributeRules_Model_Resource_AttributeRule_Collection $collection */
        $collection = Mage::getResourceModel("gh_attributerules/attributeRule_collection");
        $collection->addVendorFilter($this->getVendor());
        if (!$all) { // If all is NOT selected
            $collection->addRuleIdFilter($rules);
        }
        $collection->load();
        // --Collecting rules

        // TODO: if no rules

        $usedAttr = array(); // Used attributes
        $dataByProduct = array();
        /** @var GH_AttributeRules_Model_AttributeRule $rule */
        foreach ($collection as $rule) {
            // Collecting attributes to update
            $filter = $rule->getFilterArray();
            $ruleAttr = $gridModel->getAttribute($rule->getColumn());
            $usedAttr[$ruleAttr->getId()] = $ruleAttr;

            // Preparing product collection
            $prodColl = $this->_prepareCollection($store, $attributeSetId);
            if (count($productIds)) {
                $prodColl->addIdFilter($productIds);
            }
            // --Preparing product collection
            if ($filter) { // Some filter, process for it
                foreach ($filter as $typeKey => $item) {
                    foreach ($item as $key => $condition) {
                        /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
                        $attr = $gridModel->getAttribute($key);
                        if ($attr->getAttributeCode() == "name") {
                            $prodColl->addFieldToFilter(array(
                                array("attribute" => "name", "filter" => $condition),
                                array("attribute" => "skuv", "filter" => $condition)
                            ));
                            continue;
                        }
                        $prodColl->addAttributeToFilter($attr, $condition);
                    }
                }
            }
            foreach ($prodColl->getAllIds() as $id) {
                if ($ruleAttr->getFrontendInput() == "multiselect") {
                    $dataByProduct[$id][$ruleAttr->getAttributeCode()][] = $rule->getValue();
                } elseif ($ruleAttr->getFrontendInput() == "select") {
                    $dataByProduct[$id][$ruleAttr->getAttributeCode()][0] = $rule->getValue(); // Overwrite
                }
            }
            // --Collecting attributes to update
        }

        // TODO: load product collection with used attributes
        // TODO: merge current attributes for product (should work like add for multiselect, set for select)
        // TODO: updateAttributesNoIndex
        // TODO: reindexAfterMassAttributeChange

    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {
        return Mage::getModel("udropship/session")->getVendor();
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param int $attributeSetId
     * @return Zolago_Catalog_Model_Resource_Vendor_Product_Collection
     */
    protected function _prepareCollection($store, $attributeSetId) {
        /** @var Zolago_Catalog_Model_Resource_Vendor_Product_Collection $prodColl */
        $prodColl = Mage::getResourceModel("zolagocatalog/vendor_product_collection");
        $prodColl->setFlag("skip_price_data", true);
        $prodColl->setStoreId($store->getId());
        $prodColl->addStoreFilter($store);

        // Add non-grid filters
        $prodColl->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId());
        $prodColl->addAttributeToFilter("attribute_set_id", $attributeSetId);
        $prodColl->addAttributeToFilter("visibility", array("in" => array(
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
            Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH
        )));
        return $prodColl;
    }

    /**
     * @return Zolago_Catalog_Model_Vendor_Product_Grid
     */
    public function getGridModel() {
        return Mage::getSingleton('zolagocatalog/vendor_product_grid');
    }
}