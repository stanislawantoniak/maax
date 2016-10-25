<?php

/**
 * Class Zolago_Catalog_Model_Queue_Configurable
 */
class Zolago_Catalog_Model_Queue_Configurable extends Zolago_Common_Model_Queue_Abstract
{

    public function _construct()
    {
        //parent::_construct();
        $this->_init('zolagocatalog/queue_configurable');
    }

    /**
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _getItem()
    {
        return Mage::getModel('zolagocatalog/queue_item_configurable');
    }
    public function process($limit = 0) {
        $limit = $limit? $limit:$this->_limit;
        $this->_getCollection();
        if (!count($this->_collection)) {
            // empty queue
            return 0;
        }
        $this->_collection->setPageSize($limit);
        $this->_execute();

        return count($this->_collection);
    }

    protected function _execute()
    {

        $collection = $this->_collection;
        $collection->setOrder('insert_date','ASC');

        $listUpdatedProducts = array();
        $listUpdatedQueue = array();
        $listProductsIds = array();

        $data = $collection->getData();

        if(empty($data)){
            return;
        }
        foreach ($data as $colItem) {
            $productId = $colItem['product_id'];
            $queueId = $colItem['queue_id'];

            $listUpdatedProducts[$productId] = $productId;
            $listProductsIds[] = $productId;
            $listUpdatedQueue[$queueId] = $queueId;
        }
        unset($productId);
        unset($queueId);

        /* @var $zolagoCatalogProductConfigurableModel Zolago_Catalog_Model_Resource_Product_Configurable */
        $zolagoCatalogProductConfigurableModel = Mage::getResourceModel('zolagocatalog/product_configurable');

        //define parent products (configurable) by child (simple)
        $configurableSimpleRelation = $zolagoCatalogProductConfigurableModel->getConfigurableSimpleRelation($listUpdatedProducts);

        $productsIdsPullToSolr = array();

        if (!empty($configurableSimpleRelation)) {
            $configurableProducts = array_keys($configurableSimpleRelation);

            //1. Set attributes price, msrp, options
            $productsIdsPullToSolrForWebsite = $zolagoCatalogProductConfigurableModel->updateConfigurableProductsValues($configurableProducts);
            $productsIdsPullToSolr = array_merge($productsIdsPullToSolr, $productsIdsPullToSolrForWebsite);

            $zolagoCatalogProductConfigurableModel->removeUpdatedRows($listUpdatedQueue);

            //2. set SALE/PROMO FLAG
            $zolagoCatalogProductConfigurableModel->updateSalePromoFlag($configurableProducts);
        }

        $productsIdsPullToSolr =  array_merge($productsIdsPullToSolr, $listProductsIds);


        //3. reindex products
        //to avoid long queries make number of queries
        $numberQ = 100;
        if (count($productsIdsPullToSolr) > $numberQ) {
            $productsToReindexC = array_chunk($productsIdsPullToSolr, $numberQ);
            foreach ($productsToReindexC as $productsToReindexCItem) {
                Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindexCItem);

            }
            unset($productsToReindexCItem);
        } else {
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsIdsPullToSolr);

        }


        //4. put products to solr queue
        //catalog_converter_price_update_after
        Mage::dispatchEvent(
            "catalog_converter_price_update_after",
            array(
                "product_ids" => $productsIdsPullToSolr
            )
        );


        //5. Varnish & Turpentine
        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->addFieldToFilter('entity_id', array('in' => $productsIdsPullToSolr));
        $coll->addAttributeToFilter("visibility", array('in' =>
            array(Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)));

        Mage::dispatchEvent(
            "catalog_converter_queue_configurable_complete",
            array("products" => $coll)
        );

    }


}