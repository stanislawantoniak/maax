<?php

class Zolago_Catalog_Block_Product_List_Crosssell extends Mage_Catalog_Block_Product_List_Crosssell {
    /**
     * Prepare crosssell items data
     *
     * @return Mage_Catalog_Block_Product_List_Crosssell
     */
    protected function _prepareData()
    {
        $product = Mage::registry('product');
        /* @var $product Mage_Catalog_Model_Product */

        $this->_itemCollection = $product->getCrossSellProductCollection()
            ->addAttributeToSelect(Mage::getSingleton('catalog/config')->getProductAttributes())
            ->addAttributeToSelect('campaign_regular_id')//for strikeout price from campaign
            ->addAttributeToSelect('campaign_strikeout_price_type')//for strikeout price from campaign
            ->addAttributeToSelect('skuv')//for strikeout price from campaign
            ->addAttributeToSelect('product_flag', "left")//for strikeout price from campaign
            ->setPositionOrder()
            ->addStoreFilter();

        Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_itemCollection);
        //Mage::getSingleton('catalog/product_status')->addSaleableFilterToCollection($this->_itemCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($this->_itemCollection);

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * return shorted to $n letters escaped name of product for visual purpose
     *
     * @param Zolago_Catalog_Model_Product $item
     * @param int $n
     * @return string
     */
    public function getShortProductName(Zolago_Catalog_Model_Product $item, $n) {
        $productName = $this->escapeHtml($item->getName());
        if (strlen($productName) > 50) {
            $productName = substr($productName, 0, $n) . '...';
        }
        return $productName;
    }
}