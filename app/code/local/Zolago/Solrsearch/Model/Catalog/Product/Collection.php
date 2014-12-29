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
        $profiler = Mage::helper("zolagocommon/profiler");
        /* @var $profiler Zolago_Common_Helper_Profiler */

        //$profiler->start();

        $data = $this->getSolrData("response", "docs");
        if (!empty($data) && empty($data['error'])) {

            //$profiler->log("Solr");
            foreach($data as $item) {
                // Build product product
                $prodcut = Mage::getModel("zolagosolrsearch/catalog_product");
                // Map attributes
                // @todo ajax error //psiwik

                /*
                Item (Zolago_Solrsearch_Model_Catalog_Product) with the same id "0" already exist

                #0 /home/sheeva/www/zolago/magento/Zolago/app/code/local/Zolago/Solrsearch/Model/Catalog/Product/Collection.php(69): Varien_Data_Collection->addItem(Object(Zolago_Solrsearch_Model_Catalog_Product))
                #1 /home/sheeva/www/zolago/magento/Zolago/lib/Varien/Data/Collection.php(622): Zolago_Solrsearch_Model_Catalog_Product_Collection->loadData(false, false)
                #2 /home/sheeva/www/zolago/magento/Zolago/app/code/local/Zolago/Solrsearch/Model/Catalog/Product/Collection.php(49): Varien_Data_Collection->load(false, false)
                #3 /home/sheeva/www/zolago/magento/Zolago/app/code/local/Zolago/Solrsearch/Model/Catalog/Product/List.php(37): Zolago_Solrsearch_Model_Catalog_Product_Collection->load()
                #4 /home/sheeva/www/zolago/magento/Zolago/app/code/local/Orba/Common/controllers/Ajax/ListingController.php(84): Zolago_Solrsearch_Model_Catalog_Product_List->getCollection()
                #5 /home/sheeva/www/zolago/magento/Zolago/app/code/local/Orba/Common/controllers/Ajax/ListingController.php(55): Orba_Common_Ajax_ListingController->_getProducts(Object(Zolago_Solrsearch_Model_Catalog_Product_List))
                #6 /home/sheeva/www/zolago/magento/Zolago/app/code/core/Mage/Core/Controller/Varien/Action.php(418): Orba_Common_Ajax_ListingController->get_productsAction()
                #7 /home/sheeva/www/zolago/magento/Zolago/app/code/core/Mage/Core/Controller/Varien/Router/Standard.php(250): Mage_Core_Controller_Varien_Action->dispatch('get_products')
                #8 /home/sheeva/www/zolago/magento/Zolago/app/code/core/Mage/Core/Controller/Varien/Front.php(172): Mage_Core_Controller_Varien_Router_Standard->match(Object(Mage_Core_Controller_Request_Http))
                #9 /home/sheeva/www/zolago/magento/Zolago/app/code/core/Mage/Core/Model/App.php(354): Mage_Core_Controller_Varien_Front->dispatch()
                #10 /home/sheeva/www/zolago/magento/Zolago/app/Mage.php(684): Mage_Core_Model_App->run(Array)
                #11 /home/sheeva/www/zolago/magento/Zolago/index.php(88): Mage::run('default', 'store')
                #12 {main}
                */
                Mage::helper('zolagosolrsearch')->mapSolrDocToProduct($item, $prodcut);
                $this->addItem($prodcut);
            }

            //$profiler->log("Adding items");

            // Add urls, prices, i like it for all collection
            $this->_loadAttributesData();
        }

        //$profiler->log("Attributes loaded");

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