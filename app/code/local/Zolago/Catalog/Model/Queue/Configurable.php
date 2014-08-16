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

    protected function _execute()
    {

        $hash = md5(microtime());

        Mage::log(microtime() . "{$hash} Start ", 0, "configurable_update_{$hash}_A.log");
        $collection = $this->_collection;

        $websites = array();
        $listUpdatedProducts = array();


        foreach ($collection as $colItem) {
            Mage::log($colItem->getData(), 0, "configurable_update_{$hash}_collection.log");
            $productId = $colItem->getProductId();
            $websiteId = $colItem->getWebsiteId();

            $websites[$websiteId] = $websiteId;
            //$listUpdatedProducts[$productId] = $productId;
        }

        $model = Mage::getModel('zolagocatalog/queue_configurable');
        $collection = $model->getCollection();
        $idsSelect = clone $collection->getSelect();
        $idsSelect->reset(Zend_Db_Select::ORDER);
        $idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
        $idsSelect->reset(Zend_Db_Select::COLUMNS);
        $idsSelect->where("status=0");
        $idsSelect->columns('product_id', 'main_table');
        $idsSelect->limit(5000);
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $listUpdatedProducts = $readConnection->fetchCol($idsSelect);

        unset($productId);

        $storeId = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $storeId[] = $_storeId;
        }
        $zolagoCatalogModelProductConfigurableData = Mage::getResourceModel('zolagocatalog/product_configurable');

        //define parent products (configurable) by child (simple)
        Mage::log($listUpdatedProducts, 0, "configurable_update_{$hash}_listUpdatedProducts.log");
        $configurableSimpleRelation = $zolagoCatalogModelProductConfigurableData->getConfigurableSimpleRelation(
            $listUpdatedProducts,$hash
        );

        if (empty($configurableSimpleRelation)) {
            Mage::log("Found 0 configurable products ", 0, "configurable_update_{$hash}_A.log");
            return;
        }

        $relations = count($configurableSimpleRelation);

        $configurableProductsIds = array_keys($configurableSimpleRelation);

        //min prices
        $minPrices = array();
        foreach ($storeId as $store) {
            $minPrices[$store] = $zolagoCatalogModelProductConfigurableData
                ->getConfigurableMinPrice($configurableProductsIds, $store, $hash);
        }
        Mage::log('minPrices', 0, "configurable_update_{$hash}_minPrices.log");
        Mage::log($minPrices, 0, "configurable_update_{$hash}_minPrices.log");
        //--min prices


        //super attribute ids
        $superAttributes = $zolagoCatalogModelProductConfigurableData->getSuperAttributes();
        Mage::log($superAttributes, 0, "configurable_update_{$hash}_superAttributes.log");
        //--super attribute ids


        $productAction = Mage::getSingleton('catalog/product_action');
        $productConfigurableIds = array();
        Mage::log($configurableSimpleRelation, 0, "configurable_update_{$hash}_configurableSimpleRelation.log");



        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {


            //update configurable product price
            foreach ($storeId as $store) {
                $productMinPrice = isset($minPrices[Mage_Core_Model_App::ADMIN_STORE_ID][$productConfigurableId])
                    ? $minPrices[Mage_Core_Model_App::ADMIN_STORE_ID][$productConfigurableId]['min_price'] : false;

                if ($productMinPrice) {
                    $productAction->updateAttributesNoIndex(
                        array($productConfigurableId), array('price' => $productMinPrice), $store
                    );

                    $superAttributeId = isset($superAttributes[$productConfigurableId])
                        ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : false;

                    if ($superAttributeId) {
                        $zolagoCatalogModelProductConfigurableData->insertProductSuperAttributePricing(
                            $productConfigurableId, $superAttributeId, $productMinPrice, $store
                        );

                        $productConfigurableIds[] = $productConfigurableId;
                    }
                }

            }


        }


        Mage::log(microtime() . "{$hash} Reindex ", 0, "configurable_update_{$hash}.log");


        $productsToReindex = array_merge($listUpdatedProducts, $productConfigurableIds);
        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds($productsToReindex);
        $indexers = array(
            'source'  => Mage::getResourceModel('catalog/product_indexer_eav_source'),
            'decimal' => Mage::getResourceModel('catalog/product_indexer_eav_decimal'),
        );
        foreach ($indexers as $indexer) {
            /** @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract */
            $indexer->reindexEntities($productsToReindex);
        }
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $fI = new Mage_Catalog_Model_Resource_Product_Flat_Indexer();
            $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeID, 'price');
            foreach ($storeId as $storesId) {
                $fI->updateAttribute($attribute, $storesId, $productsToReindex);
            }
        }

        //zolago_catalog_after_update_price_type
        Mage::dispatchEvent(
            "zolago_catalog_after_update_price_type",
            array(
                 "product_ids" => $listUpdatedProducts
            )
        );
        Mage::log(microtime() . "{$hash} End ", 0, "configurable_update_{$hash}.log");


    }
}