<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Zolago_Catalog') . DS . "Vendor" . DS . "ProductController.php";

/**
 * Class GH_AttributeRules_MassController
 * require_once @see Zolago_Catalog_Vendor_ProductController
 */
class GH_AttributeRules_MassController extends Zolago_Catalog_Vendor_ProductController {

    /**
     * Mass auto fill attributes
     * Note: apply rules to selected
     */
    public function autofillAction() {
        // Input data
        $req = $this->getRequest();
        $all = $req->getParam("all", 0);
        $global = (int)$req->getParam("global", 0); // All products flag on grid
        $attributes = $req->getParam("attributes", array());
        $values = $req->getParam("values", array());
        $rules = $req->getParam("rules", array());
        $storeId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        $productIds = $req->getParam("product_ids");
        $attributeSetId = $req->getParam("attribute_set_id");
        $query = $req->getParams();
        $static = $req->getParam("static");

        try {
            $store = Mage::app()->getStore($storeId);

            if (!$attributeSetId) {
                Mage::throwException("Technical error: no attribute set id specified");
            }
            if (!$this->getVendor()->getId()) {
                Mage::throwException("Security notice: You have no right to do this action");
            }

            if (is_string($productIds)) {
                $productIds = explode(",", $productIds);
            }
            if (is_array($productIds) && count($productIds)) {
                $productIds = array_filter(array_unique($productIds));
            }
            if (!(is_array($productIds) && count($productIds)) || $global) {
                $restQuery = $this->_getRestQuery($query);
                $collection = $this->_getCollection();
                foreach($restQuery as $key => $value){
                    $collection->addAttributeToFilter($key, $value);
                }
				// get all ids
				$productIds = array();
				$idName = $collection->getEntity()->getIdFieldName();
				foreach ($collection->getData() as $row) {
					$productIds[] = (int)$row[$idName];
				}
            }

            /** @var $gridModel Zolago_Catalog_Model_Vendor_Product_Grid */
            $gridModel = $this->getGridModel();

            // Collecting rules
            /** @var GH_AttributeRules_Model_Resource_AttributeRule_Collection $collection */
            $collection = Mage::getResourceModel("gh_attributerules/attributeRule_collection");
            $collection->addVendorFilter($this->getVendor());
            if (!$all) { // If all is NOT selected
                if (is_array($rules) && !empty($rules)) {
                    $collection->addRuleIdFilter($rules);
                } else {
                    Mage::throwException("Please select any rules");
                }
            }
            $collection->load();
            // --Collecting rules

            if (!$collection->count()) {
                Mage::throwException("Please select any rules");
            }

            $usedAttr = array(); // Used attributes
            $dataByProduct = array();
            /** @var GH_AttributeRules_Model_AttributeRule $rule */
            foreach ($collection as $rule) {
                // Collecting attributes to update
                $filter = $rule->getFilterArray();
                $ruleAttr = $gridModel->getAttribute($rule->getColumn());
                if (!$this->getGridModel()->isAttributeEditable($ruleAttr) || ($ruleAttr->getIsRequired() && trim($rule->getValue()) == "")) {
                    continue; // Skip not editable on grid or empty value when required
                }
                if (Mage::helper("zolagocatalog/attribute")->isAttrNotBlockedForMass($ruleAttr)) {
                    continue; // Skip blocked attribute for mass change (like product name)
                }
                $usedAttr[$ruleAttr->getAttributeCode()] = $ruleAttr;

                // Preparing product collection
                $prodColl = $this->_prepareBasicCollectionForUpdate($store, $attributeSetId);
                if (count($productIds)) {
                    $prodColl->addIdFilter($productIds);
                } else {
                    break; // Filtering must be
                }
                // --Preparing product collection
                if ($filter) { // Some filter, process for it
                    foreach ($filter as $typeKey => $item) {
                        foreach ($item as $key => $condition) {
                            /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
                            $attr = $gridModel->getAttribute($key);
                            if ($attr->getAttributeCode() == "name") {
                                $prodColl->addAttributeToFilter(array(
                                    array("attribute" => "name", "filter" => $condition),
                                    array("attribute" => "skuv", "filter" => $condition)
                                ), null, 'left');
                                continue;
                            }
                            if (isset($condition['null'])) {
                                $this->_addNull($prodColl, $attr, $condition['null']);
                                continue;
                            }
                            if (isset($condition["regexp"])) {
                                $this->_addRegexp($prodColl, $attr, $condition["regexp"]);
                                continue;
                            }
                            $prodColl->addAttributeToFilter($attr, $condition);
                        }
                    }
                }
                foreach ($prodColl->getAllIds() as $id) {
                    if ($ruleAttr->getFrontendInput() == "multiselect") {
                        $dataByProduct[(int)$id][$ruleAttr->getAttributeCode()][] = $rule->getValue();
                    } elseif ($ruleAttr->getFrontendInput() == "select") {
                        $dataByProduct[(int)$id][$ruleAttr->getAttributeCode()][0] = $rule->getValue(); // Overwrite
                    }
                }
                // --Collecting attributes to update
            }

            // Skip if nothing to update
            if (!empty($dataByProduct)) {
                // dataByProduct now look like:
                // array
                //  32929 =>
                //    array
                //      'child_age' =>
                //        array
                //          0 => string '1992'
                //          1 => string '1991'
                //      'color' =>
                //        array
                //          0 => string '8'
                //      'manufacturer' =>
                //        array
                //          0 => string '2025'
                //  32934 =>
                //    array
                //      'manufacturer' =>
                //        array
                //          0 => string '1035'
                //  32938 =>
                //    array
                //      'manufacturer' =>
                //        array
                //          0 => string '1035'
                //      'color' =>
                //        array
                //          0 => string '737'


                // Load product collection with used attributes
                $prodColl = $this->_prepareBasicCollectionForUpdate($store, $attributeSetId);
                $prodColl->addAttributeToSelect(array_keys($usedAttr), "left");
                $prodColl->addIdFilter($productIds);

                // Merge current attributes for product (should work like add for multiselect, set for select)
                $dataForUpdate = array();
                $prodDatas = $prodColl->getData(); // No load for better performance
                foreach ($prodDatas as $product) {
                    foreach ($usedAttr as $attr) {
                        $code = $attr->getAttributeCode();
                        $prodId = (int)$product["entity_id"];
                        if ($attr->getFrontendInput() == "multiselect") {
                            if (isset($dataByProduct[$prodId]) && isset($dataByProduct[$prodId][$code])) {
                                $tmp = array_filter(explode(",", $product[$code]));
                                $newValue = array_unique(array_merge(!empty($tmp) ? $tmp : array(), $dataByProduct[$prodId][$code]));
                                sort($newValue);
                                $dataForUpdate[$prodId][$attr->getAttributeCode()] =
                                    implode(",", $newValue);
                            }
                        } elseif ($attr->getFrontendInput() == "select") {
                            if (isset($dataByProduct[$prodId]) && isset($dataByProduct[$prodId][$code])) {
                                $dataForUpdate[$prodId][$attr->getAttributeCode()] = implode(",", $dataByProduct[$prodId][$code]);
                            }
                        }
                    }
                }
                // Now we have merged old values and new values like:
                // array
                //  32929 =>
                //    array
                //      'child_age' => string '1987,1991,1992' // NOTE: 1987 was on product previously
                //      'color' => string '737'
                //      'manufacturer' => string '2025'
                //  32934 =>
                //    array
                //      'manufacturer' => string '1035'
                //  32938 =>
                //    array
                //      'color' => string '737'
                //      'manufacturer' => string '1035'


                $dataForReindex = array();
                foreach ($dataForUpdate as $productId => $attribs) {
                    foreach ($attribs as $code => $value) {
                        $dataForReindex[$code][$value][] = $productId;
                    }
                }
                // $dataForReindex now looks like:
                // array
                //  'child_age' =>
                //    array
                //      '1987,1991,1992' =>
                //        array
                //          0 => int 32929
                //  'color' =>
                //    array (size=1)
                //      737 =>
                //        array
                //          0 => int 32929
                //          1 => int 32938
                //  'manufacturer' =>
                //    array
                //      2025 =>
                //        array
                //          0 => int 32929
                //      1035 =>
                //        array
                //          0 => int 32934
                //          1 => int 32938


                // Update Attributes No Index
                /** @var Zolago_Catalog_Model_Product_Action $productAction */
                $productAction = Mage::getSingleton('catalog/product_action');

                $idsForReindex = array();

                foreach ($dataForReindex as $code => $item) {
                    foreach ($item as $value => $ids) {
                        if (!empty($ids)) {
                            $idsForReindex = array_merge($idsForReindex, $ids);
                            $productAction->updateAttributesPure($ids, array($code => $value), $storeId);
                        }
                    }
                }

                if (!empty($idsForReindex)) {
                    $ids = array_unique($idsForReindex);

                    $indexer = new Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source();
                    /* @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Source */
                    $indexer->reindexEntities($ids);

                    // Push to solr and ban varnish
                    Mage::dispatchEvent(
                        "mass_autofill_attribute_rules_after",
                        array(
                            "product_ids" => $ids
                        )
                    );
                }
            }
        } catch (Exception $e) {
            $this->getResponse()->setHttpResponseCode(500);
            $result = array(
                'status'        => 0,
                'message'       => Mage::helper("gh_attributerules")->__($e->getMessage()),
                'changed_ids'	=> array(),
				'global'		=> $global
            );
        }

        if (!isset($result)) {
            $result = array(
                'status'        => 1,
                'message'       => Mage::helper("gh_attributerules")->__("Autofill rules processed %s products", isset($ids) ? count($ids) : 0),
                'changed_ids'   => $productIds,
                'global'        => $global
            );
        }

        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @param Mage_Core_Model_Store $store
     * @param int $attributeSetId
     * @return Zolago_Catalog_Model_Resource_Vendor_Product_Collection
     */
    protected function _prepareBasicCollectionForUpdate($store, $attributeSetId) {
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
     * Add filter for empty values
     *
     * @param Zolago_Catalog_Model_Resource_Vendor_Product_Collection $collection
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param array $value
     * @return Zolago_Catalog_Model_Resource_Vendor_Product_Collection
     */
    protected function _addNull($collection, $attribute, $value) {
        $collection->addAttributeToFilter(array(
            array("attribute" => $attribute->getAttributeCode(), "filter" => array("null" => true)),
            array("attribute" => $attribute->getAttributeCode(), "filter" => array("eq" => ""))
        ), null, 'left');
        return $collection;
    }

    /**
     * Add filter for multiselect attributes by regexp
     * Inspired by @see Zolago_Catalog_Controller_Vendor_Product_Abstract::_getSqlCondition()
     *
     * @param Zolago_Catalog_Model_Resource_Vendor_Product_Collection $collection
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param array $value
     * @return Zolago_Catalog_Model_Resource_Vendor_Product_Collection
     */
    protected function _addRegexp($collection, $attribute, $value) {

        $code               = $attribute->getAttributeCode();
        $aliasCode          = $code ."_filter";
        $valueTableDefault  = "at_".$aliasCode."_default";
        $valueTable         = "at_".$aliasCode;

        $collection->joinAttribute($aliasCode, "catalog_product/$code", "entity_id", null, "left");
        if ($collection->getStoreId()) {
            $valueExpr = $collection->getSelect()->getAdapter()
                ->getCheckSql("{$valueTable}.value_id > 0", "{$valueTable}.value", "{$valueTableDefault}.value");
        } else {
            $valueExpr = "$valueTable.value";
        }
        // Try use regexp to match vales with boundary (like comma, ^, $)  - (123,456,678)
        $collection->getSelect()->where($valueExpr . " REGEXP ?", $value);
        return $collection;
    }

    public function removeruleAction() {
        $ruleId = $this->getRequest()->getQuery("id");
        $vendor = $this->getVendor();

        /** @var GH_AttributeRules_Model_AttributeRule $rule */
        $rule = Mage::getModel("gh_attributerules/attributeRule")->load($ruleId);
        if ($rule->getId() && $rule->getVendorId() == $vendor->getId()) {
            $rule->delete();
            $result = array(
                'status' => 1,
                'message'=> Mage::helper("gh_attributerules")->__("Rule removed")
            );
        } elseif (!$rule->getId()) {
            $result = array(
                'status' => 1,
                'message'=> Mage::helper("gh_attributerules")->__("Rule removed")
            );
        } else {
            $result = array(
                'status' => 0,
                'message'=> Mage::helper("gh_attributerules")->__("This autofill rule don't belongs to you")
            );
        }
        $this->getResponse()
            ->clearHeaders()
            ->setHeader('Content-type', 'application/json')
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }
}