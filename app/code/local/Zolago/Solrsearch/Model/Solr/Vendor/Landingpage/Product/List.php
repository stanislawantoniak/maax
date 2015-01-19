<?php
class Zolago_Solrsearch_Model_Solr_Vendor_Landingpage_Product_List extends Zolago_Solrsearch_Model_Catalog_Product_List
{
    /**
     * @return Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function getCollection() {
        if(!$this->getData("collection")) {
            $collection = Mage::getModel("zolagosolrsearch/catalog_product_collection"); /* @var $collection Zolago_Solrsearch_Model_Catalog_Product_Collection */
            $collection->setFlag("store_id", Mage::app()->getStore()->getId());
            $data = $this->getSolrData();
            if (is_array($data)) {
                $collection->setSolrData($this->getSolrData());
            }
            $collection->load();
            $this->setData("collection", $collection);
        }
        return $this->getData("collection");
    }

    public function getSolrData() {
        $solrData = Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
        if (!$solrData) {
            $queryText = $this->getQueryText();
            Mage::getModel('zolagosolrsearch/solr_vendor_landingpage_product_solr')->queryRegister($queryText);
        }
        return Mage::registry(Zolago_Solrsearch_Model_Solr::REGISTER_KEY);
    }

    /**
     * Query param: rows
     * @return int
     */
    public function getCurrentLimit() {
        return (int) Mage::getStoreConfig(
            "zolagomodago_catalog/zolagomodago_cataloglisting/vendor_landingpage_n_products",
            Mage::app()->getStore());
    }


}