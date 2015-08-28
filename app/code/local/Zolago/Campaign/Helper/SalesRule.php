<?php

class Zolago_Campaign_Helper_SalesRule extends Mage_Core_Helper_Abstract
{
    protected $_productAttributes = null;
    protected $_productsCollection = null;
    protected $_salesRuleCollection = null;

    /**
     * Clearing SalesRule Cart conditions from
     * conditions corresponding to cart (quota) etc
     *
     * @param $conditions
     * @return mixed
     */
    public function cleanConditions(&$conditions) {

        if (isset($conditions["conditions"])) {
            self::cleanConditions($conditions["conditions"]);
        } else {
            foreach ($conditions as $key => &$condition) {
                if ($condition["type"] == "salesrule/rule_condition_address") {
                    unset($conditions[$key]);
                }
                elseif (!isset($condition["conditions"]) && !empty($condition["attribute"])) {
                    if (preg_match("/quote|qty|total/",
                        $condition["attribute"])) {
                        unset($conditions[$key]);
                    }
                }
                elseif (isset($condition["conditions"])) {
                    self::cleanConditions($condition["conditions"]);
                }
            }
        }
        return $conditions;
    }

    /**
     * @return null|Zolago_Catalog_Model_Resource_Product_Collection
     */
    public function getProductsDataForWebsites() {

        if ($this->_productsCollection === null) {
            /** @var Mage_Core_Model_Website $website */
            foreach (Mage::app()->getWebsites() as $website) {

                // Campaign is assigned for website
                /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
                $coll = Mage::getResourceModel("zolagocatalog/product_collection");
                $coll->addAttributeToFilter("visibility", array("neq" => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
                $coll->addAttributeToFilter("status", array("eq" => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
                $coll->addAttributeToFilter('type_id', array("in" => array(
                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)));
                $coll->addWebsiteFilter($website);

                $coll->getSelect()->joinLeft(
                    array('product_relation' => $coll->getTable("catalog/product_relation")),
                    'product_relation.child_id = e.entity_id',
                    array(
                        'parent_id' => 'product_relation.parent_id',
                    )
                );

                $prodAttr = $this->getProductAttributes();
                /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
                foreach ($prodAttr as $attr) {
                    $coll->addAttributeToSelect($attr->getAttributeCode(), "left");
                }
                $this->_productsCollection[$website->getId()] = $coll->getData();
            }
        }
        return $this->_productsCollection;
    }

    /**
     * Get valid for campaign porpoise SalesRule collection
     *
     * @return Mage_SalesRule_Model_Resource_Rule_Collection|null
     */
    public function getSalesRuleCollection() {
        if ($this->_salesRuleCollection === null) {

            $now = Mage::getModel('core/date')->date('Y-m-d');
            /** @var Mage_SalesRule_Model_Resource_Rule_Collection $rulesColl */
            $rulesColl = Mage::getResourceModel("salesrule/rule_collection");
            $rulesColl->addIsActiveFilter(); // Active only
            $rulesColl->addFieldToFilter("from_date", array("lteq" => $now)); // <=
            $rulesColl->addFieldToFilter("to_date",   array("gteq" => $now)); // >=
            $rulesColl->addFieldToFilter("campaign_id", array("gt" => 0));    // >

            $this->_salesRuleCollection = $rulesColl;
        }
        return $this->_salesRuleCollection;
    }

    /**
     * Get product attributes for correct SalesRule validation process
     *
     * @return array|null
     */
    public function getProductAttributes() {
        if ($this->_productAttributes === null) {
            /** @var Zolago_Catalog_Model_Resource_Product $catalogProductResource */
            $catalogProductResource = Mage::getResourceSingleton('catalog/product');
            $catalogProductResource->loadAllAttributes();
            $productAttributes = $catalogProductResource->getAttributesById();

            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            foreach ($productAttributes as $id => $attribute) {

                if (!$attribute->isAllowedForRuleCondition()
                    || !$attribute->getDataUsingMethod("is_used_for_promo_rules")
                    || preg_match("/quote|qty|total/", $attribute->getAttributeCode()
                    || !in_array((int)$attribute->getIsGlobal(), array(
                            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
                            Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_WEBSITE
                        )))
                ) {
                    unset($productAttributes[$id]);
                }
            }
            $this->_productAttributes = $productAttributes;
        }
        return $this->_productAttributes;
    }
}