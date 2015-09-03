<?php

/**
 * Improved collection based on solr-recived data
 */
class Zolago_Solrsearch_Model_Catalog_Product_Collection extends Varien_Data_Collection {

    protected $_solrData;


    /**
     * @var Mage_Catalog_Model_Category
     */
    protected $_currentCategory;


    /**
     * @param Mage_Catalog_Model_Category $category
     * @return \Zolago_Solrsearch_Model_Catalog_Product_Collection
     */
    public function setCurrentCategory(Mage_Catalog_Model_Category $category) {
        $this->_currentCategory = $category;
        return $this;
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    public function getCurrentCategory() {
        return $this->_currentCategory;
    }

    /**
     * @param array $doc
     */
    public function setSolrData(array $doc) {
        $this->_solrData = $doc;
    }

    /**
     * Load data from solr current query
     * @param type $printQuery
     * @param type $logQuery
     */
    public function load($printQuery = false, $logQuery = false) {

        if(!$this->_isCollectionLoaded) {
            $this->_setIsLoaded(true);
            parent::load($printQuery, $logQuery);
        }
        return $this;
    }

    public function loadData($printQuery = false, $logQuery = false) {

        $data = $this->getSolrData("response", "docs");
        if (!empty($data) && empty($data['error'])) {
            /** @var Zolago_Solrsearch_Helper_Data $helper */
            $helper = Mage::helper('zolagosolrsearch');
            foreach($data as $item) {
                // Build product
                /** @var Zolago_Solrsearch_Model_Catalog_Product $product */
                $product = Mage::getModel("zolagosolrsearch/catalog_product");
                // Map attributes
                $helper->mapSolrDocToProduct($item, $product);
                if ($product->getId()) {
                    // Add price
                    $helper->mapSolrDocPriceToProduct($item, $product);
                    $this->addItem($product);
                }
            }

            // Add urls and `in my wishlist` for all collection
            $this->_loadAttributesData();
        }

        return parent::loadData($printQuery, $logQuery);
    }

    protected function _loadAttributesData() {
        $storeId = $this->getFlag("store_id");
        $customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();

        $resource = Mage::getResourceSingleton('zolagosolrsearch/improve');
        /* @var $resource Zolago_Solrsearch_Model_Resource_Improve */


        $resource->loadAttributesDataForFrontend($this, $storeId, $customerGroupId);

    }

    /**
     * @return int
     */
    public function getSize() {
        if(null!==$this->getSolrData("response", "numFound")) {
            return $this->getSolrData("response", "numFound");
        }
        return parent::getSize();
    }

    /**
     * @param index, .... otional
     * @return array | mixed
     */
    public function getSolrData() {
        $currnetDoc = $this->_solrData;;
        foreach(func_get_args() as $arg) {
            if(isset($currnetDoc[$arg])) {
                $currnetDoc = $currnetDoc[$arg];
            } else {
                return null;
            }
        }
        return $currnetDoc;
    }
}