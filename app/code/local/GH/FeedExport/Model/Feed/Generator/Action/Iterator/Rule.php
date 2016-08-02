<?php

class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Rule extends Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator_Rule
{

    public function getCollection()
    {
        $feed = $this->getFeed();

        //Pre-Filters
        $productStatus = $feed->getProductStatus();
        $productVisibility = $feed->getProductVisibility();
        $productTypeId = $feed->getProductTypeId();
        $productInventoryIsInStock = $feed->getProductInventoryIsInStock();

        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addStoreFilter();

        if (!empty($productStatus))
            $collection->addFieldToFilter("status", $productStatus);

        if (!empty($productVisibility))
            $collection->addFieldToFilter("visibility", $productVisibility);

        if (!empty($productTypeId))
            $collection->addFieldToFilter("type_id", $productTypeId);

        ////////
        $storeId = $feed->getStoreId();

        if ($productInventoryIsInStock) {
            $collection = Mage::getModel("ghfeedexport/observer")->joinStockData($storeId, $collection);
            if ($productInventoryIsInStock == GH_FeedExport_Model_Observer::FILTER_STOCK_IN_STOCK){
                $collection->addFieldToFilter("is_in_stock", Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK);
            }
            if ($productInventoryIsInStock == GH_FeedExport_Model_Observer::FILTER_STOCK_OUT_OF_STOCK){
                $collection->addFieldToFilter("is_in_stock", Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK);
            }
        }
        //////////////

        Mage::app()->getStore()->setId(0);
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

        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
            && $stockItem->getManageStock() == 0
        ) {

            $rule = Mage::getModel('feedexport/rule')->load($this->getId());

            $conditions_serialized = unserialize($rule->getData("conditions_serialized"));

            $conditions = isset($conditions_serialized["conditions"]) ? $conditions_serialized["conditions"] : array();;

            $checkConfigurableStock = false;
            if (!empty($conditions)) {
                foreach ($conditions as $condition) {

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
                    $childStockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($child->getId());
                    if ($childStockItem->getData("is_in_stock") == 1) {

                        $isChildInStock = 1;
                        break;
                    }
                }

                if ($isChildInStock == 0) {
                    $stock = false;
                }

            }
        }


        if ($valid && $stock) {
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