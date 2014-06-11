<?php


class Zolago_Catalog_Model_Resource_Stock_Data extends Mage_Core_Model_Resource_Db_Abstract
{


    public  function _construct()
    {
        $this->_init('zolagopos/pos');
    }

    /**
     * @param $insertData
     */
    public function saveCatalogInventoryStockItem($insertData)
    {
        $this->_getWriteAdapter()->beginTransaction();

        $this->_getWriteAdapter()->query("INSERT INTO  `cataloginventory_stock_item`
(product_id,qty,is_in_stock,stock_id)
VALUES {$insertData}
ON DUPLICATE KEY UPDATE
qty=VALUES(qty),is_in_stock=VALUES(is_in_stock),stock_id=1;");



        $this->_getWriteAdapter()->commit();

    }


    /**
     * @param $insertData
     * @param $websiteId
     */
    public function  saveCatalogInventoryStockStatus($insertData)
    {

        $this->_getWriteAdapter()->beginTransaction();
        $this->_getWriteAdapter()->query("INSERT INTO  `cataloginventory_stock_status`
(product_id,qty,stock_status,stock_id,website_id)
VALUES {$insertData}
ON DUPLICATE KEY UPDATE
qty=VALUES(qty),stock_status=VALUES(stock_status),stock_id=1;");
        $this->_getWriteAdapter()->commit();
    }


    /**
     * Calculate stock on open orders
     * @param $merchant
     * @return array
     */
    public function calculateStockOpenOrders($merchant)
    {
        $adapter = $this->getReadConnection();
        $select = $adapter->select();
        $select
            ->from('udropship_po_item AS po_item',
                array(
                    'sku' => 'po_item.sku',
                    'qty' => 'SUM(po_item.qty)'
                )
            )
            ->join(
                array('products' => 'catalog_product_entity'),
                'po_item.product_id=products.entity_id',
                array()
            )
            ->join(
                array('po' => 'udropship_po'),
                'po.entity_id=po_item.parent_id',
                array()
            )
            ->where("products.type_id=?", Mage_Catalog_Model_Product_Type::TYPE_SIMPLE)
            ->where("po.udropship_vendor=?", (int)$merchant)
            ->where('po.udropship_status NOT IN (?)', array(11, 2, 1, 6, 7, 10, 13, 12))
            ->group('po_item.sku');

        $result = $adapter->fetchAssoc($select);

        return $result;
    }



}