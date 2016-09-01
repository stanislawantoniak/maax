<?php

/**
 * Class GH_FeedExport_Model_Observer
 */
class GH_FeedExport_Model_Observer
{
    const FILTER_STOCK_IN_STOCK = 1;
    const FILTER_STOCK_OUT_OF_STOCK = 2;



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
            new Zend_Db_Expr('cataloginventory_stock_status.stock_status'),
            array()
        );

        $select->join(
            array('cataloginventory_stock_table' => $stockTable),
            "e.entity_id = cataloginventory_stock_table.product_id",
            array()
        );

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
}