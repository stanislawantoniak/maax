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

        Mage::log(microtime() . "{$hash} Start ", 0, 'configurable_update.log');
        $collection = $this->_collection;

        $websites = array();
        $listUpdatedProducts = array();


        foreach ($collection as $colItem) {
            $productId = $colItem->getProductId();
            $websiteId = $colItem->getWebsiteId();

            $websites[$websiteId] = $websiteId;
            $listUpdatedProducts[$productId] = $productId;
        }
        unset($productId);

        $storeId = array(Mage_Core_Model_App::ADMIN_STORE_ID);
        $allStores = Mage::app()->getStores();
        foreach ($allStores as $_eachStoreId => $val) {
            $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
            $storeId[] = $_storeId;
        }
        $zolagoCatalogModelProductConfigurableData = Mage::getResourceModel('zolagocatalog/product_configurable');

        //define parent products (configurable) by child (simple)
        $configurableSimpleRelation = $zolagoCatalogModelProductConfigurableData->getConfigurableSimpleRelation(
            $listUpdatedProducts
        );
        if (empty($configurableSimpleRelation)) {
            Mage::log(microtime() . "{$hash} Found 0 configurable products ", 0, 'configurable_update.log');
            return;
        }

        $relations = count($configurableSimpleRelation);

        $configurableProductsIds = array_keys($configurableSimpleRelation);

        //min prices
        $minPrices = array();
        foreach ($storeId as $store) {
            $minPrices[$store] = $zolagoCatalogModelProductConfigurableData
                ->getConfigurableMinPrice($configurableProductsIds, $store);
        }

        //--min prices


        //super attribute ids
        $superAttributes = $zolagoCatalogModelProductConfigurableData->getSuperAttributes();
        //--super attribute ids


        $productAction = Mage::getSingleton('catalog/product_action');
        $productConfigurableIds = array();
        Mage::log(microtime() . "{$hash} {$relations} relations found ", 0, 'configurable_update.log');
        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {


            //update configurable product price
            foreach ($storeId as $store) {
                $productMinPrice = isset($minPrices[$store][$productConfigurableId])
                    ? $minPrices[$store][$productConfigurableId]['min_price'] : false;

                if ($productMinPrice) {
                    $productAction->updateAttributesNoIndex(
                        array($productConfigurableId), array('price' => $productMinPrice), $store
                    );

                    $superAttributeId = isset($superAttributes[$productConfigurableId])
                        ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : false;
                    Mage::log($superAttributeId, 0, 'configurable_update.log');
                    if ($superAttributeId) {
                        $zolagoCatalogModelProductConfigurableData->insertProductSuperAttributePricing(
                            $productConfigurableId, $superAttributeId, $productMinPrice, $store
                        );

                        $productConfigurableIds[] = $productConfigurableId;
                    }
                }

            }


        }


        Mage::log(microtime() . "{$hash} Reindex ", 0, 'configurable_update.log');


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
        Mage::log(microtime() . "{$hash} End ", 0, 'configurable_update.log');


    }
}