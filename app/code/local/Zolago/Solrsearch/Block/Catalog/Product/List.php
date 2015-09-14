<?php
class Zolago_Solrsearch_Block_Catalog_Product_List extends Mage_Catalog_Block_Product_List {

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection() {
        return $this->getListModel()->getCollection();
    }

    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_List
     */
    public function getListModel() {
        return Mage::getSingleton('zolagosolrsearch/catalog_product_list');
    }

    /**
     * @return string
     */
    public function getJsonProducts($products=null) {
        return Mage::helper("core")->jsonEncode(
                   is_null($products) ?
	                   Mage::helper("zolagosolrsearch")->prepareAjaxProducts($this->getListModel()) :
	                   $products
               );
    }

    /**
     * Returns how many products will be loaded on start.
     *
     * @return int
     */
    public function getLoadLimit()
    {
        $limit = (int) Mage::getStoreConfig("zolagomodago_catalog/zolagomodago_cataloglisting/load_on_start"
                                            , Mage::app()->getStore());

        if ($limit === 0) {
            $limit = Zolago_Solrsearch_Model_Catalog_Product_List::DEFAULT_LIMIT;
        }

        return $limit;
    }

}
