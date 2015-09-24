<?php
/**
 * Stock item resource model
 *
 * @category    Zolago
 * @package     Zolago_CatalogInventory
 */
class Zolago_CatalogInventory_Model_Resource_Stock_Item
    extends Mage_CatalogInventory_Model_Resource_Stock_Item
{
    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId()
    {
        return Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;
    }

    /**
     * Define main table and initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory/stock_item', 'item_id');
    }

    /**
     * Update inventory stock
     *
     * @param $insertData
     * @return $this
     * @throws Exception
     */
    public function saveCatalogInventoryStockItem($insertData)
    {
        $insertLines = implode(',', $insertData);

        $this->beginTransaction();
        try {
            $stockId = $this->getStockId();

            $insert = sprintf(
                "INSERT INTO %s (product_id,qty,is_in_stock,stock_id) VALUES %s "
                . " ON DUPLICATE KEY UPDATE qty=VALUES(qty),is_in_stock=VALUES(is_in_stock),stock_id=%s",
                $this->getMainTable(), $insertLines, $stockId
            );

            $this->_getWriteAdapter()->query($insert);
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->rollBack();
            Mage::logException($e);
        }

        return $this;
    }

}