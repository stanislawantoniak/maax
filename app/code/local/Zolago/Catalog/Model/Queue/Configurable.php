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

        //$this->_collection->setLockRecords();
        $this->_execute();
        //$this->_collection->setDoneRecords();

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


        $storeId = array();
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $storeId[] = $_storeId;
        }
        $zolagoCatalogModelProductConfigurableData = Mage::getResourceModel('zolagocatalog/product_configurable');

        //define parent products (configurable) by child (simple)
        $configurableSimpleRelation = $zolagoCatalogModelProductConfigurableData->getConfigurableSimpleRelation($listUpdatedProducts);

        if (empty($configurableSimpleRelation)) {
            //Mage::log("Found 0 configurable products ", 0, "configurable_update.log");
            return;
        }
        $configurableProducts = array_keys($configurableSimpleRelation);


        $productsIdsPullToSolr = array();

        //1. Set attributes price, msrp, options
//        $websites = Mage::app()->getWebsites();
//        foreach ($websites as $website) {
//            $productsIdsPullToSolrForWebsite = $zolagoCatalogModelProductConfigurableData->updateConfigurableProductsValues($website, $configurableProducts);
//            $productsIdsPullToSolr = array_merge($productsIdsPullToSolr, $productsIdsPullToSolrForWebsite);
//        }


        //super attribute ids
        $superAttributes = $zolagoCatalogModelProductConfigurableData->getSuperAttributes($configurableProducts);
        //--super attribute ids

        $productsConfigurableIds = array();
        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {
            $superAttributeId = isset($superAttributes[$productConfigurableId])
                ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : false;
            if ($superAttributeId) {
                $productsConfigurableIds[] = $productConfigurableId;
            }
        }
        $campaignResModel = Mage::getResourceModel('zolagocampaign/campaign'); /** @var $campaignResModel Zolago_Campaign_Model_Resource_Campaign*/
        $listProductsIds = array_merge($listProductsIds, $productsConfigurableIds);
        $productConfigurableIdsForCampaignCheck = $campaignResModel->getIsProductsInValidCampaign($listProductsIds);


        $productConfigurableIds = array();

        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {
            $superAttributeId = isset($superAttributes[$productConfigurableId])
                ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : false;
            if ($superAttributeId) {
                //update configurable product price

                //if product is in campaign(promo or sale)
                //here attr price should not be touched
                if (!in_array($productConfigurableId, $productConfigurableIdsForCampaignCheck) ) {
                    $zolagoCatalogModelProductConfigurableData->insertProductSuperAttributePricingApp(
                        $productConfigurableId, $superAttributeId, $storeId
                    );
                }

                $productConfigurableIds[$productConfigurableId] = $productConfigurableId;
            }

        }
        $zolagoCatalogModelProductConfigurableData->removeUpdatedRows($listUpdatedQueue);
        //1. reindex prices
        $productsToReindex = array_merge($listUpdatedProducts, $productConfigurableIds);

        //1. reindex products
        //to avoid long queries make number of queries
//        Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindex);
        $numberQ = 100;
        if (count($productsToReindex) > $numberQ) {
            $productsToReindexC = array_chunk($productsToReindex, $numberQ);
            foreach ($productsToReindexC as $productsToReindexCItem) {
                Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindexCItem);

            }
            unset($productsToReindexCItem);
        } else {
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindex);

        }

        //2. put products to solr queue
        //catalog_converter_price_update_after
        Mage::log('catalog_converter_price_update_after', 0, 'configurable_update_solr.log');
        Mage::dispatchEvent(
            "catalog_converter_price_update_after",
            array(
                "product_ids" => $productsToReindex
            )
        );

        // Varnish & Turpentine
        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $coll = Mage::getResourceModel('zolagocatalog/product_collection');
        $coll->addFieldToFilter('entity_id', array( 'in' => $productsToReindex));
        $coll->addAttributeToFilter("visibility", array('in' =>
            array( Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH,
                Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)));

        Mage::dispatchEvent(
            "catalog_converter_queue_configurable_complete",
            array("products" => $coll)
        );
    }


}