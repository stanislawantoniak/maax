<?php
/**
 * qty and is_in_stock by website
 */
class Zolago_CatalogInventory_Model_Stock extends Mage_CatalogInventory_Model_Stock {
    /**
     * Add stock item objects to products
     *
     * @param   collection $products
     * @return  Mage_CatalogInventory_Model_Stock
     */
    public function addItemsToProducts($productCollection)
    {
        $items = $this->getItemCollection()
            ->addProductsFilter($productCollection)
            ->joinStockStatus($productCollection->getStoreId())
            ->joinStockWebsite($productCollection->getStoreId());
        $items->getSelect()->columns(array('qty_status' => 'status_table.qty'));
        $items->load();
        $stockItems = array();
        foreach ($items as $item) {
            $item->setIsInStock($item->getWebsiteIsInStock());
            $item->setQty($item->getQtyStatus());
            $stockItems[$item->getProductId()] = $item;
        }
        foreach ($productCollection as $product) {
            if (isset($stockItems[$product->getId()])) {
                $stockItems[$product->getId()]->assignProduct($product);
            }
        }
        return $this;
    }

}