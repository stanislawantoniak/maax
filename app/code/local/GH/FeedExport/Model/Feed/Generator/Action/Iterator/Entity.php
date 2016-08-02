<?php

/**
 * Class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Entity
 */
class GH_FeedExport_Model_Feed_Generator_Action_Iterator_Entity
    extends Mirasvit_FeedExport_Model_Feed_Generator_Action_Iterator_Entity
{


    public function joinStockData($storeId, $collection)
    {

        $select = $collection->getSelect();
        $adapter = $select->getAdapter();


        $stockTable = $collection->getTable('cataloginventory/stock_item');
        $stockStatusTable = $collection->getTable('cataloginventory/stock_status');
        $linkTable = $collection->getTable("catalog/product_super_link");

        // Join stock item from stock index
        $websiteId = Mage::getModel('core/store')->load($storeId)->getWebsiteId();
        $select->joinLeft(
            array('cataloginventory_stock_status' => $stockStatusTable),
            '(cataloginventory_stock_status.product_id=e.entity_id) AND (' . $adapter->quoteInto("cataloginventory_stock_status.stock_id=?", Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID) .
            ' AND ' . $adapter->quoteInto("cataloginventory_stock_status.website_id=?", $websiteId) . ')',
            array()
        // array('is_in_stock'=>new Zend_Db_Expr('IFNULL(stock_status, 0)'))
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
        $subSelect->where("child_stock_available.is_in_stock=?", Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK);
        $collection->addExpressionAttributeToSelect('available_child_count',
            "IF(e.type_id IN ('configurable', 'grouped'), (" . $subSelect . "), null)", array());

        // Join child qtys
        $subSelect = $adapter->select();
        $subSelect->from(array("link_qty" => $linkTable), array("IFNULL(SUM(child_qty.qty),0)"));
        $subSelect->join(
            array("child_qty" => $stockTable),
            "link_qty.product_id=child_qty.product_id", array());
        $subSelect->where("link_qty.parent_id=e.entity_id");
        $subSelect->where("child_qty.is_in_stock=?", Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK);

        // Use subselect only for parent products
        $collection->addExpressionAttributeToSelect('stock_qty',
            "IF(e.type_id IN ('configurable', 'grouped'), (" . $subSelect . "), IFNULL($stockStatusTable.qty,0))", array());
        return $collection;
    }

    public function getCollection()
    {
        if ($this->_type == 'product') {

            $feed = $this->getFeed();

            //Pre-Filters
            $productStatus = $feed->getProductStatus();
            $productVisibility = $feed->getProductVisibility();
            $productTypeId = $feed->getProductTypeId();
            $productInventoryIsInStock = $feed->getProductInventoryIsInStock();

            $collection = Mage::getModel('catalog/product')->getCollection()
                ->joinField('qty', 'cataloginventory/stock_item', 'qty',
                    'product_id=entity_id', '{{table}}.stock_id=1', 'left')
                ->addFieldToFilter("sku",
                    array("in" => array("9-T0CCQ4FLV","9-TOTGWW", "9-TOTGWX") )
                )
                ->addStoreFilter();

            if (!empty($productStatus))
                $collection->addFieldToFilter("status", $productStatus);

            if (!empty($productVisibility))
                $collection->addFieldToFilter("visibility", $productVisibility);

            if (!empty($productTypeId))
                $collection->addFieldToFilter("type_id", $productTypeId);

            ////////
            $storeId = $feed->getStoreId();
            $collection = $this->joinStockData($storeId, $collection);
            if ($productInventoryIsInStock) {
                if ($productInventoryIsInStock == GH_FeedExport_Model_Observer::FILTER_STOCK_IN_STOCK){
                    $collection->addFieldToFilter("is_in_stock", Mage_CatalogInventory_Model_Stock::STOCK_IN_STOCK);
                }
                if ($productInventoryIsInStock == GH_FeedExport_Model_Observer::FILTER_STOCK_OUT_OF_STOCK){
                    $collection->addFieldToFilter("is_in_stock", Mage_CatalogInventory_Model_Stock::STOCK_OUT_OF_STOCK);
                }
            }
            //////////////


            if (count($this->getFeed()->getRuleIds()) || Mage::app()->getRequest()->getParam('skip')) {
                $collection->getSelect()->joinLeft(
                    array('rule' => Mage::getSingleton('core/resource')->getTableName('feedexport/feed_product')),
                    'e.entity_id=rule.product_id', array())
                    ->where('rule.feed_id = ?', $this->getFeed()->getId())
                    ->where('rule.is_new = 1');
            }
        } elseif ($this->_type == 'category') {
            $root = Mage::getModel('catalog/category')->load($this->getFeed()->getStore()->getRootCategoryId());

            $collection = Mage::getModel('catalog/category')->getCollection()
                ->addAttributeToSelect('name')
                ->addFieldToFilter('entity_id', array('nin' => array(1, 2)));

            if (method_exists($collection, 'addPathFilter')) {
                $collection->addPathFilter($root->getPath());
            }
        } elseif ($this->_type == 'review') {
            $collection = Mage::getModel('review/review')->getResourceCollection();

            $collection->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addFieldToFilter('entity_id', 1)
                ->setDateOrder()
                ->addRateVotes()
                ->load();
        }
        Mage::log($collection->getData(),null, "xxx.log");
        return $collection;
    }
}