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
        $collection = $this->_collection;

        $listUpdatedProducts = array();


        foreach ($collection as $colItem) {
            $productId = $colItem->getProductId();

            $listUpdatedProducts[$productId] = $productId;
        }
        unset($productId);
        Mage::log("Config collection: " . implode(',',$listUpdatedProducts), 0, "configurable_update_conf.log");
        $storeId = array(Mage_Core_Model_App::ADMIN_STORE_ID);
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
            //return;
        }
        $configurableProducts = array_keys($configurableSimpleRelation);
        Mage::log("configurableProducts: " . implode(',',$configurableProducts), 0, "configurable_update_conf.log");

        //super attribute ids
        $superAttributes = $zolagoCatalogModelProductConfigurableData->getSuperAttributes($configurableProducts);
        //--super attribute ids
        $productConfigurableIds = array();

        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {
            //update configurable product price
            foreach ($storeId as $store) {

                $superAttributeId = isset($superAttributes[$productConfigurableId])
                    ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : false;

                if ($superAttributeId) {

                    $zolagoCatalogModelProductConfigurableData->insertProductSuperAttributePricingApp(
                        $productConfigurableId, $superAttributeId, $store
                    );

                    $productConfigurableIds[] = $productConfigurableId;
                }

            }
        }


        //1. reindex prices
        $productsToReindex = array_merge($listUpdatedProducts, $productConfigurableIds);

        //1. reindex products
        Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($productsToReindex);

        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $fI = new Mage_Catalog_Model_Resource_Product_Flat_Indexer();
            $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeID, 'price');
            foreach ($storeId as $storesId) {
                $fI->updateAttribute($attribute, $storesId, $productsToReindex);
            }
        }

        //2. put products to solr queue
        //zolago_catalog_after_update_price_type
        Mage::dispatchEvent(
            "zolago_catalog_after_update_price_type",
            array(
                 "product_ids" => $listUpdatedProducts
            )
        );


    }
}