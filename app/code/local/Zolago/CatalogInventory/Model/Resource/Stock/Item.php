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

    protected function _afterLoad(Mage_Core_Model_Abstract $object) {
        $productId = $object->getProductId();
        $resource = $object->getResource();
        $posStockTable = $resource->getTable("zolagopos/stock");
        $select = $resource->getReadConnection()->select()
                  ->from(array("pos_stock" => $posStockTable),array('qty'))
                  ->join(
                      array('pos' => $resource->getTable("zolagopos/pos")),
                      "pos.pos_id = pos_stock.pos_id",
                      array('pos_id','name')
                  )
                  ->join(
                      array('pos_website' => $resource->getTable("zolagopos/pos_vendor_website")),
                      "pos_website.pos_id = pos.pos_id",
                      array('website_id')
                  )
                  ->joinLeft(
                      array('stock_website' => $resource->getTable("zolagocataloginventory/stock_website")),
                      "stock_website.product_id = pos_stock.product_id AND stock_website.website_id = pos_website.website_id",
                      array('is_in_stock')
                  )
                  ->where("pos_stock.product_id=?", $productId)
                  ->where("pos.is_active = ?", Zolago_Pos_Model_Pos::STATUS_ACTIVE);

        $result = $resource->getReadConnection()->fetchAll($select);
        $out = array();
        $sum = array();
        $instock = array();
        $pos = array();
        $websitePos = array();
        if ($result) {
            foreach ($result as $item) {
                $sum[$item['pos_id']] = $item['qty'];
                $pos[$item['pos_id']] = array(
                                            'name' => $item['name'],
                                            'qty'  => $item['qty']
                                        );
                $websitePos[$item['website_id']][] = $item['pos_id'];
                if (!isset($out[$item['website_id']])) {
                    $out[$item['website_id']] = 0;
                }
                $out[$item['website_id']] += $item['qty'];
                $instock[$item['website_id']] = empty($item['is_in_stock'])? 0:1;
            }
        }
        $out[0] = array_sum($sum); // admin website
        $object->setWebsiteQty($out);
        $object->setInStock($instock);
        $object->setPosQty($pos);
        $object->setWebsitePos($websitePos);
        return parent::_afterLoad($object);
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

    /**
     * update is_in_stock by website
     * @param array productIds
     * @return $this
     */
    public function updateAvailability($productIds) {
        if (empty($productIds)) {
            return $this;
        }        
        $resource = Mage::getSingleton('core/resource');
        $adapter = $resource->getConnection('core_write');
        // clear all status
        $query = sprintf('UPDATE %s SET is_in_stock = 0 WHERE product_id IN (%s)',
            $this->getTable('zolagocataloginventory/stock_website'),
            implode(',',$productIds));
        $adapter->query($query);
        // update new availability
        $queryString = 'INSERT INTO %s (website_id,product_id,is_in_stock) '.
                    'SELECT * FROM (SELECT pos_website.website_id,stock_pos.product_id,IF(SUM(IFNULL(stock_pos.qty,0))>0,1,0) as is_in_stock '.
                    'FROM %s as stock_pos  '.
                    'INNER JOIN %s as pos ON pos.pos_id = stock_pos.pos_id '.
                    'INNER JOIN %s as pos_website ON pos_website.pos_id = stock_pos.pos_id '.
                    'WHERE pos.is_active = %s '.
                        'AND stock_pos.product_id IN (%s) '.                         
                    'GROUP BY stock_pos.product_id,pos_website.website_id) as subselect '.
                    'ON DUPLICATE KEY UPDATE is_in_stock = subselect.is_in_stock';
        $query = sprintf($queryString,
            $this->getTable('zolagocataloginventory/stock_website'),
            $this->getTable('zolagopos/stock'),
            $this->getTable('zolagopos/pos'),
            $this->getTable('zolagopos/pos_vendor_website'),
            Zolago_Pos_Model_Pos::STATUS_ACTIVE,
            implode(',',$productIds)
        );
        $adapter->query($query);
    }

}