<?php

/**
 * Class Zolago_CatalogInventory_Model_Stock_Item
 */
class Zolago_CatalogInventory_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item {


    /**
     * Adding stock data to product
     *
     * @param   Mage_Catalog_Model_Product $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function assignProduct(Mage_Catalog_Model_Product $product)
    {

        if (!$this->getId() || !$this->getProductId()) {
            $this->_getResource()->loadByProductId($this, $product->getId());
            $this->setOrigData();
        }

        $this->setProduct($product);
        $product->setStockItem($this);

        $product->setIsInStock($this->getIsInStock());
        Mage::getSingleton('cataloginventory/stock_status')
            ->assignProduct($product, $this->getStockId(), $this->getStockStatusInPos());

        if ($this->getAlwaysInStock()) {
            $product->setIsSalable(true);
        }
        return $this;
    }

    public function getStockStatusInPos()
    {
        $stockStatus = $this->getStockStatus();
        if (!is_null($stockStatus)) { //simple product
            $productId = $this->getProductId();

//            SELECT
//  `cataloginventory_stock_status`.`product_id`,
//IF(IFNULL(SUM(pos_stock.qty), 0) > 0, 1, 0) AS stock_status
//FROM
//  `cataloginventory_stock_status`
//
//  LEFT JOIN `zolago_pos_stock` AS `pos_stock`
//    ON pos_stock.product_id = cataloginventory_stock_status.product_id
//  LEFT JOIN `zolago_pos` AS `pos`
//    ON pos.pos_id = pos_stock.pos_id
//  LEFT JOIN `zolago_pos_vendor_website` AS `pos_website`
//    ON pos_website.website_id = cataloginventory_stock_status.website_id
//WHERE (
//    cataloginventory_stock_status.product_id IN ('33712')
//  )
//  AND (stock_id = 1)
//            AND (
//            cataloginventory_stock_status.website_id = 1
//            )
//            AND (pos_website.website_id = 1)
//            AND (pos.is_active = 1)
//            AND  pos_stock.`pos_id`=pos.`pos_id`
//            AND  pos_website.`pos_id`=pos.pos_id
//            AND`cataloginventory_stock_status`.`product_id`
//GROUP BY `cataloginventory_stock_status`.`product_id`


            //TODO add logic for POSes

            $select = $this->getResource()->getReadConnection()->select()
                ->from("cataloginventory_stock_status",
                    array(
                        'product_id',
                        //'stock_status',
                        'IF(IFNULL(SUM(pos_stock.qty), 0) > 0, 1, 0) AS stock_status'
                    )
                )

                ->joinLeft(
                    array('pos_stock' => $this->getResource()->getTable("zolagopos/stock")),
                    "pos_stock.product_id = cataloginventory_stock_status.product_id",
                    array()
                )
                ->joinLeft(
                    array('pos' => $this->getResource()->getTable("zolagopos/pos")),
                    "pos.pos_id = pos_stock.pos_id",
                    array()
                )
                ->joinLeft(
                    array('pos_website' => $this->getResource()->getTable("zolagopos/pos_vendor_website")),
                    "pos_website.website_id = cataloginventory_stock_status.website_id",
                    array()
                )
                ->where('cataloginventory_stock_status.product_id=?', $productId)
                ->where('stock_id=?', 1)
                ->where('cataloginventory_stock_status.website_id=?', Mage::app()->getWebsite()->getId())
                ->where('pos_website.website_id=?', Mage::app()->getWebsite()->getId())
                ->where("pos.is_active = ?" , Zolago_Pos_Model_Pos::STATUS_ACTIVE)

                ->where("pos_stock.pos_id=pos.pos_id")
                ->where("pos_website.pos_id=pos.pos_id")
                ->group('cataloginventory_stock_status.product_id');
            $result = $this->getResource()->getReadConnection()->fetchRow($select);
            Mage::log((string)$select, null, "555.log");
            $stockStatus = $result["stock_status"];

        }
        return $stockStatus;
    }

}