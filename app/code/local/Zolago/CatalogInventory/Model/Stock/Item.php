<?php

/**
 * Class Zolago_CatalogInventory_Model_Stock_Item
 */
class Zolago_CatalogInventory_Model_Stock_Item extends Unirgy_Dropship_Model_Stock_Item {


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

            //logic for POSes

            $resource = $this->getResource();
            $posStockTable = $resource->getTable("zolagopos/stock");
            $select = $resource->getReadConnection()->select()
                ->from(array("pos_stock" => $posStockTable),
                    array(
                        'product_id',
                        "stock_status" => new Zend_Db_Expr("IF(IFNULL(SUM(pos_stock.qty), 0) > 0, 1, 0)")
                    )
                )
                ->joinLeft(
                    array('pos' => $resource->getTable("zolagopos/pos")),
                    "pos.pos_id = pos_stock.pos_id",
                    array()
                )
                ->joinLeft(
                    array('pos_website' => $resource->getTable("zolagopos/pos_vendor_website")),
                    "pos_website.pos_id = pos.pos_id",
                    array()
                )
                ->where("pos_stock.product_id=?", $productId)
                ->where('pos_website.website_id=?', Mage::app()->getWebsite()->getId())
                ->where("pos.is_active = ?", Zolago_Pos_Model_Pos::STATUS_ACTIVE)
                ->group("pos_stock.product_id");

            $result = $resource->getReadConnection()->fetchRow($select);

            $stockStatus = isset($result["stock_status"]) ? $result["stock_status"] : 0;

        }
        return $stockStatus;
    }

}