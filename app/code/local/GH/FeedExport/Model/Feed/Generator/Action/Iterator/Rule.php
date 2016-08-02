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


        if ($productInventoryIsInStock) {
            if ($productInventoryIsInStock == GH_FeedExport_Model_Observer::FILTER_STOCK_IN_STOCK)
                $this->joinStockData($feed, $collection, Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK);

            if ($productInventoryIsInStock == GH_FeedExport_Model_Observer::FILTER_STOCK_OUT_OF_STOCK)
                $this->joinStockData($feed, $collection, Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK);
        }

        Mage::app()->getStore()->setId(0);
        $this->_rule->getConditions()->collectValidatedAttributes($collection);

        return $collection;
    }



    /**
     * @param $feed
     * @param $collection
     * @param $stockValue (Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK or Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK)
     * @return mixed
     */
    public function joinStockData($feed, $collection, $stockValue)
    {

        $select = $collection->getSelect();
        $adapter = $select->getAdapter();


        $stockTable = $collection->getTable('cataloginventory/stock_item');
        $stockStatusTable = $collection->getTable('cataloginventory/stock_status');
        $linkTable = $collection->getTable("catalog/product_super_link");

        // Join stock item from stock index
        $websiteId = Mage::getModel('core/store')->load($feed->getStoreId())->getWebsiteId();
        $select->joinLeft(
            array('cataloginventory_stock_status' => $stockStatusTable),
            '(cataloginventory_stock_status.product_id=e.entity_id) AND (' . $adapter->quoteInto("cataloginventory_stock_status.stock_id=?", Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID) .
            ' AND ' . $adapter->quoteInto("cataloginventory_stock_status.website_id=?", $websiteId) . ')',
            array()
        );
        $collection->addExpressionAttributeToSelect('is_in_stock',
            new Zend_Db_Expr('IFNULL(cataloginventory_stock_status.stock_status, 0)'),
            array()
        );

        $select->join(
            array('cataloginventory_stock_table' => $stockTable),
            "e.entity_id = cataloginventory_stock_table.product_id",
            array()
        );
        $collection->addExpressionAttributeToSelect('politics',
            "IF(e.type_id IN ('configurable', 'grouped'), (cataloginventory_stock_table.manage_stock = 1 AND cataloginventory_stock_table.is_in_stock = 0) , (cataloginventory_stock_table.min_qty>999999) )", array());
        // Join all children count
        $subSelect = $adapter->select();
        $subSelect->from(array("link_all" => $linkTable), array("COUNT(link_all.link_id)"));
        $subSelect->where("link_all.parent_id=e.entity_id");

        $collection->addExpressionAttributeToSelect('all_child_count', $subSelect, array());

        // Join available child count
        $subSelect = $adapter->select();
        $subSelect->from(array("link_available" => $linkTable), array("COUNT(link_available.link_id)"));
        $subSelect->join(
            array("child_stock_available" => $stockTable),
            "link_available.product_id=child_stock_available.product_id",
            array());
        $subSelect->where("link_available.parent_id=e.entity_id");
        $subSelect->where("child_stock_available.is_in_stock=?", $stockValue);
        $collection->addExpressionAttributeToSelect('available_child_count',
            "IF(e.type_id IN ('configurable', 'grouped'), (" . $subSelect . "), null)", array());

        // Join child qtys
        $subSelect = $adapter->select();
        $subSelect->from(array("link_qty" => $linkTable), array("IFNULL(SUM(child_qty.qty),0)"));
        $subSelect->join(
            array("child_qty" => $stockTable),
            "link_qty.product_id=child_qty.product_id", array());
        $subSelect->where("link_qty.parent_id=e.entity_id");
        $subSelect->where("child_qty.is_in_stock=?", $stockValue);

        // Use subselect only for parent products
        $collection->addExpressionAttributeToSelect('stock_qty',
            "IF(e.type_id IN ('configurable', 'grouped'), (" . $subSelect . "), IFNULL($stockStatusTable.qty,0))", array());
        return $collection;
    }

//    public function callback($row)
//    {
//        Mage::log($row, null, "callbackkk.log");
//        $check = null;
//        $valid = false;
//        $stock = true;
//
//        $product = Mage::getModel('catalog/product');
//        $product->setData($row);
//
//        if ($this->_rule->getConditions()->validate($product)) {
//            $valid = true;
//        }
//
//        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId());
//        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
//            && $stockItem->getManageStock() == 0
//        ) {
//
//            $rule = Mage::getModel('feedexport/rule')->load($this->getId());
//
//            $conditions_serialized = unserialize($rule->getData("conditions_serialized"));
//
//            $conditions = isset($conditions_serialized["conditions"]) ? $conditions_serialized["conditions"] : array();
//
//            $checkConfigurableStock = false;
//            if (!empty($conditions)) {
//                foreach ($conditions as $condition) {
//
//                    if ($condition["attribute"] == "is_in_stock"
//                        && $condition["operator"] == "=="
//                        && $condition["value"] == 1
//                    ) {
//                        $checkConfigurableStock = true;
//                        continue;
//                    }
//                }
//            }
//            if ($checkConfigurableStock) {
//                $stock = ($row["available_child_count"] > 0) ? true : false;
//            }
//        }
//
//
//        if ($valid && $stock) {
//            $check = $product->getId();
//        }
//        return $check;
//    }

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