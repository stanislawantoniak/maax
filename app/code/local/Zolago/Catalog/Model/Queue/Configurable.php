<?php

class Zolago_Catalog_Model_Queue_Configurable extends Zolago_Common_Model_Queue_Abstract
{


    public function _construct()
    {
        //parent::_construct();
        $this->_init('zolagocatalog/queue_configurable');
    }

    protected function _getItem() {
        return Mage::getModel('zolagocatalog/queue_item_configurable');
    }

    protected function _execute() {

        $hash = md5(microtime());

        Mage::log(microtime()."{$hash} Start ", 0, 'configurable_update.log');
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




        $storeId = array(0,1,2);


        $zolagoCatalogModelProductConfigurableData = Mage::getModel('zolagocatalog/product_configurable_data');


        //define parent products (configurable) by child (simple)
        $configurableSimpleRelation = $zolagoCatalogModelProductConfigurableData->getConfigurableSimpleRelation($listUpdatedProducts);
        if (empty($configurableSimpleRelation)) {
            Mage::log(microtime()."{$hash} Found 0 configurable products ", 0, 'configurable_update.log');
            return;
        }

        $relations = count($configurableSimpleRelation);

        $configurableProductsIds = array_keys($configurableSimpleRelation);

        //min prices
        $minPrices = array();
        foreach($storeId as $store){
            $minPrices[$store] = $zolagoCatalogModelProductConfigurableData
                ->getConfigurableMinPrice($configurableProductsIds, $store);
        }

        //--min prices


        //super attribute ids
        $superAttributes = $zolagoCatalogModelProductConfigurableData->getSuperAttributes();
        //--super attribute ids



        $productAction = Mage::getSingleton('catalog/product_action');
        $productConfigurableIds = array();
        Mage::log(microtime()."{$hash} {$relations} relations found ", 0, 'configurable_update.log');
        foreach ($configurableSimpleRelation as $productConfigurableId => $configurableSimpleRelationItem) {


            //update configurable product price
            foreach ($storeId as $store) {
                $productMinPrice = isset($minPrices[$store][$productConfigurableId]) ? $minPrices[$store][$productConfigurableId]['min_price'] : FALSE;

                if ($productMinPrice){
                    $productAction->updateAttributesNoIndex(array($productConfigurableId), array('price' => $productMinPrice), $store);

                    $superAttributeId = isset($superAttributes[$productConfigurableId]) ? (int)$superAttributes[$productConfigurableId]['super_attribute'] : FALSE;

                    if ($superAttributeId) {
                        $zolagoCatalogModelProductConfigurableData->insertProductSuperAttributePricing($productConfigurableId, $superAttributeId, $productMinPrice, $store);

                        $productConfigurableIds[] = $productConfigurableId;
                    }
                }

            }




        }


        Mage::log(microtime()."{$hash} Reindex ", 0, 'configurable_update.log');


        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds($productConfigurableIds);

        Mage::log(microtime()."{$hash} End ", 0, 'configurable_update.log');


    }





}