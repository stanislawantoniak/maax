<?php
/**
 * Class GH_AttributeRules_Block_Attribute
 */
class GH_AttributeRules_Block_Attribute extends Mage_Core_Block_Template
{
    /**
     * @return GH_AttributeRules_Model_Resource_AttributeRule_Collection
     */
    public function getRulesData() {
        $attributeSetId = Mage::app()->getRequest()->getParam("attribute_set_id", 0);

        /** @var GH_AttributeRules_Model_Resource_AttributeRule_Collection $collection */
        $collection = Mage::getResourceModel("gh_attributerules/attributeRule_collection");
        $collection->addVendorFilter($this->getVendor());

        /* @var $gridModel Zolago_Catalog_Model_Vendor_Product_Grid */
        $gridModel = Mage::getModel("zolagocatalog/vendor_product_grid");


        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')
            ->setAttributeSetFilter($attributeSetId)
            ->getItems();
        $allAttributesInSet = array_keys($attributes);


        /** @var GH_AttributeRules_Model_AttributeRule $rule */
        foreach ($collection as $rule) {
            $rule->setData("attribute", $gridModel->getAttribute($rule->getColumn()));
        }

        $data = array();

        foreach ($collection as $rule) {
            $col = $rule->getColumn();
            $value = $rule->getValue();
            $attribute = $rule->getData("attribute");
            if (in_array($attribute->getId(), $allAttributesInSet)) {
                $data[$col]['attribute'] = $attribute;
                $data[$col]['value'][$value][] = $rule;
            }
            unset($attribute);
        }

        return $data;
    }

    /**
     * @return string
     */
    public function getActionUrl() {
        return $this->getUrl("udropship/mass/autofill", array("_secure" => true));
    }

    /**
     * @param GH_AttributeRules_Model_AttributeRule $rule
     * @return string
     */
    public function getRemoveRuleUrl($rule) {
        return $this->getUrl("udropship/mass/removerule",
            array(
                "_query" => array("id" => $rule->getId()),
                "_secure" => true
            ));
    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor() {
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
                foreach ($item as $key => $condition) {
                    /** @var Mage_Catalog_Model_Resource_Eav_Attribute $attr */
                    $attr = $gridModel->getAttribute($key);

                    if ($typeKey == 'regular') {
                        if (isset($condition["eq"])) {
                            $_value = $condition["eq"];
                            $str .= $attr->getStoreLabel($store) . $helper->__(" equal ");
                            $str .= $attr->getSource()->getOptionText($_value);
                        } elseif (isset($condition["regexp"])) {
                            $_value = $condition["regexp"];
                            $_value = substr($_value, 0, strlen($_value) - strlen("[[:>:]]")); // remove last part regexp
                            $_value = substr($_value, strlen("[[:<:]]"), strlen($_value)); // remove first part regexp
                            $str .= $attr->getStoreLabel($store) . $helper->__(" like '%s'", $attr->getSource()->getOptionText($_value));
                        } elseif (isset($condition["like"])) {
                            $_value = $condition["like"];
                            $_value = substr($_value, 0, strlen($_value)-1); // remove last char %
                            $_value = substr($_value, 1, strlen($_value)); // remove first char %
                            $str .= $attr->getStoreLabel($store) . $helper->__(" like '%s'", $_value);
                        } elseif (isset($condition['null'])) {
                            $str .= $attr->getStoreLabel($store) . $helper->__(" is empty");
                        }
                    } else { // ext type
                        $_value = $condition; // here condition is a value
                        $startLabel = strpos($_value, Zolago_Catalog_Helper_Data::SPECIAL_LABELS_OLD_DELIMITER);
                        if ($startLabel) {
                            $str .= $_value; // Value like <label>: <value> ex: "lepkość: średnia"
                        } else {
                            $str .= $attr->getStoreLabel($store) . ": " . $_value;
                        }
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