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

    public function getQty()
    {
        $flag = TRUE; // TODO implement by POS flag

        $qty = $this->getData('qty');
        if ($flag) {
            $qty = $this->getQtyInPos();
        }

        return $qty;
    }

    public function getStockStatusInPos()
    {

        $flag = TRUE; // TODO implement by POS flag

        $stockStatus = $this->getStockStatus();
        if (!is_null($stockStatus) && $flag) { //simple product
            //logic for POSes
            $qty = $this->getQtyInPos();
            $stockStatus = ($qty > 0) ? $qty : 0;

        }
        return $stockStatus;
    }


    /**
     * @return int
     * @throws Mage_Core_Exception
     */
    public function getQtyInPos()
    {
        $productId = $this->getProductId();

        //logic for POSes
        $resource = $this->getResource();
        $posStockTable = $resource->getTable("zolagopos/stock");
        $select = $resource->getReadConnection()->select()
            ->from(array("pos_stock" => $posStockTable),
                array("qty"
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

        $qty = $resource->getReadConnection()->fetchOne($select);

        return (int)$qty;
    }

}