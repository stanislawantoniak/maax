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
     * Retrieve product status
     * Return array as key product id, value - stock status
     *
     * @param int|array $productIds
     * @param int $websiteId
     * @param int $stockId
	 * @param bool $extend
     * @return array
     */
    public function getProductStatus($productIds, $websiteId, $stockId = 1, $extend = false)
    {
        $flag = TRUE; // TODO implement by POS flag

        if(!$flag)
            return parent::getProductStatus($productIds, $websiteId, $stockId = 1);

        if (!is_array($productIds)) {
            $productIds = array($productIds);
        }

        $posStockTable = $this->getTable("zolagopos/stock");
        $select = $this->_getReadAdapter()->select()
            ->from(array("pos_stock" => $posStockTable),
                array(
                    'product_id' => 'catalog_product_super_link.parent_id',
                    "qty" => new Zend_Db_Expr("SUM(pos_stock.qty)")
                )
            )
            ->joinLeft(
                array("catalog_product_super_link" => $this->getTable("catalog/product_super_link")),
                "pos_stock.product_id = catalog_product_super_link.product_id",
                array()
            )
			->joinLeft(
                array('pos' => $this->getTable("zolagopos/pos")),
                "pos.pos_id = pos_stock.pos_id",
                array()
            )
            ->joinLeft(
                array('pos_website' => $this->getTable("zolagopos/pos_vendor_website")),
                "pos_website.pos_id = pos.pos_id",
                array()
            )
            ->where("catalog_product_super_link.parent_id IN(?)", $productIds)

            ->where('pos_website.website_id=?', (int)$websiteId)
            ->where("pos.is_active = ?" , Zolago_Pos_Model_Pos::STATUS_ACTIVE)
            ->group("catalog_product_super_link.parent_id");

		$result = $this->_getReadAdapter()->fetchPairs($select);
		$data = array();
		foreach ($result as $id => $qty) {
			$stockStatus = (int)($qty > 0);
			if (!$extend) {
				$data[$id] = $stockStatus;
			} else {
				$data[$id] = array(
					'stock_status'	=> $stockStatus,
					'qty'			=> $qty
				);
			}
		}
        return $data;
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
            $stockId = Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID;

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