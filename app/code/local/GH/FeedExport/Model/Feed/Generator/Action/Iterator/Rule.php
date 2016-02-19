<?php

class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Rule extends Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator_Rule {


    public function getCollection()
    {
        Mage::app()->getStore()->setId(0);
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->setStoreId($this->getFeed()->getStore()->getId())
            ->addFieldToFilter('sku', array("in" => "10-04P633-5-35"));

        $this->_rule->getConditions()->collectValidatedAttributes($collection);

        return $collection;
    }

    public function callback($row)
    {
        $check = null;
        $valid = false;
        $stock = true;

        $product = Mage::getModel('catalog/product');
        $product->setData($row);

        if ($this->_rule->getConditions()->validate($product)) {
            $valid = true;
        }

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
            $rule = Mage::getModel('feedexport/rule')->load($this->getId());
            $type = $rule->getType();
            $conditions_serialized = unserialize($rule->getData("conditions_serialized"));
            //Mage::log($conditions_serialized, null, "TEST.log");
//
            $conditions = isset($conditions_serialized["conditions"]) ? $conditions_serialized["conditions"] : array();;
            //Mage::log($conditions, null, "TEST.log");

            $checkConfigurableStock = false;
            if (!empty($conditions)) {
                foreach ($conditions as $condition) {
                    //Mage::log($condition, null, "TEST2.log");
                    if ($condition["attribute"] == "is_in_stock"
                        && $condition["operator"] == "=="
                        && $condition["value"] == 1
                    ) {
                        $checkConfigurableStock = true;
                        continue;
                    }
                }
            }
            if ($checkConfigurableStock) {

                $products = $this->_getChildProducts($product);

                $isChildInStock = 0;
                foreach ($products as $child) {
                    if ($child->getData("stock_item")->getData("is_in_stock") == 1) {
                        $isChildInStock = 1;
                        break;
                    }
                }
                //Mage::log($isChildInStock, null, "TEST.log");
                if ($isChildInStock == 0) {
                    $stock = false;
                }

            }
        }


        if($valid && $stock){
            $check = $product->getId();
        }
        return $check;
    }
    protected function _getChildProducts($product)
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('read');
        $table      = Mage::getSingleton('core/resource')->getTableName('catalog_product_relation');
        $childIds   = array(0);

        $rows = $connection->fetchAll(
            'SELECT `child_id` FROM '.$table.' WHERE `parent_id` = '.intval($product->getEntityId())
        );

        foreach ($rows as $row) {
            $childIds[] = $row['child_id'];
        }

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addFieldToFilter('entity_id', array('in' => $childIds));

        return $collection;
    }

}