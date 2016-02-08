<?php

class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Landingpage_Products extends Mage_Core_Block_Template
{
    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection() {
        return $this->getListModel()->getCollection();
    }

    /**
     * @return Zolago_Solrsearch_Model_Solr_Vendor_Landingpage_Product_List
     */
    public function getListModel() {
        return Mage::getSingleton('zolagosolrsearch/solr_vendor_landingpage_product_list');
    }

    public function getSkuv(Zolago_Solrsearch_Model_Catalog_Product $product) {
        return str_replace($product->getUdropshipVendor()."-","",$product->getSku());
    }
}