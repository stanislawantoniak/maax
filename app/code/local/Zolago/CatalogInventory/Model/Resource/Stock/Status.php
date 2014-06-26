<?php
/**
 * Zolago_CatalogInventory_Model_Resource_Stock_Status Stock Status per website Resource Model
 *
 * @category    Zolago
 * @package     Zolago_CatalogInventory
 */
class Zolago_CatalogInventory_Model_Resource_Stock_Status
    extends Mage_CatalogInventory_Model_Resource_Stock_Status
{
    /**
     * Resource model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('cataloginventory/stock_status', 'product_id');
    }

    /**
     * Retrieve stock identifier
     *
     * @return int
     */
    public function getStockId()
    {
        return 1;
    }

    /**
     * Save Product Status per website
     *
     * @param array $insertData
     *
     * @throws Exception
     * @internal param int|null $websiteId
     * @return Mage_CatalogInventory_Model_Resource_Stock_Status
     */
    public function saveCatalogInventoryStockStatus($insertData)
    {
        $this->beginTransaction();
        try {
            $stockId = $this->getStockId();

            $insert = sprintf(
                "INSERT INTO %s (product_id,qty,stock_status,stock_id,website_id) VALUES %s "
                . " ON DUPLICATE KEY UPDATE qty=VALUES(qty),stock_status=VALUES(stock_status),stock_id=%s",
                $this->getMainTable(), $insertData, $stockId
            );

            $this->_getWriteAdapter()->query($insert);
            $this->_getWriteAdapter()->commit();
        } catch (Exception $e) {
            $this->rollBack();
            throw $e;
        }

        return $this;
    }
}