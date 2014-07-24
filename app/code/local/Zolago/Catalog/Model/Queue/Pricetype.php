<?php

/**
 * Class Zolago_Catalog_Model_Queue_Pricetype
 *
 * @category    Zolago
 * @package     Zolago_Catalog
 *
 */
class Zolago_Catalog_Model_Queue_Pricetype extends Zolago_Common_Model_Queue_Abstract
{

    public function _construct()
    {
        $this->_init('zolagocatalog/queue_pricetype');
    }

    /**
     * @return false|Mage_Core_Model_Abstract
     */
    protected function _getItem()
    {
        return Mage::getModel('zolagocatalog/queue_item_pricetype');
    }

    protected function _execute()
    {
        $recalculateConfigurableIds = array();
        Mage::helper('zolagocatalog/pricetype')->_logQueue( "Start process queue");
        $collection = $this->_collection;

        foreach ($collection as $colItem) {
            $productId = $colItem->getProductId();
            $ids[$productId] = $productId;
        }
        unset($productId);

        $queueModel = Mage::getResourceModel('zolagocatalog/queue_pricetype');
        $skuvs = $queueModel->getVendorSkuAssoc($ids);

        $priceTypeValues = $queueModel->getPriceTypeValues($ids);

        //reformat
        $priceTypeValueByStore =array();
        if(!empty($priceTypeValues)){
            foreach($priceTypeValues as $priceTypeValue){
                $priceTypeValueByStore[$priceTypeValue['store']][$priceTypeValue['product_id']] = $priceTypeValue['converter_price_type_label'];
            }
            unset($priceTypeValue);
        }

        $priceMarginValues = $queueModel->getPriceMarginValues($ids);

        //reformat
        $priceMarginValuesByStore = array();
        if(!empty($priceMarginValues)){
            foreach($priceMarginValues as $priceMarginValue){
                $priceMarginValuesByStore[$priceMarginValue['store']][$priceMarginValue['product_id']] = $priceMarginValue['price_margin'];
            }
            unset($priceMarginValue);
        }


        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable");
            return;
        }
        $productAction = Mage::getSingleton('catalog/product_action');
        if (!empty($skuvs)) {

            foreach ($skuvs as $productId => $productData) {
                $vendorSku = $productData['skuv'];
                $sku = $productData['sku'];

                $res = explode('-', $sku);
                $vendorExternalId = (!empty($res) && isset($res[0])) ? (int)$res[0] : false;
                if (!$vendorExternalId) {
                    return;
                }


                Mage::helper('zolagocatalog/pricetype')->_logQueue("Product {$productId}");
                $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
                $allStores = Mage::app()->getStores();
                foreach ($allStores as $_eachStoreId => $val) {
                    $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                    $stores[] = $_storeId;
                }
                foreach($stores as $store){
                    $priceType = (isset($priceTypeValueByStore[$store]) && isset($priceTypeValueByStore[$store][$productId])) ? $priceTypeValueByStore[$store][$productId] : 0;
                    if ($store <> Mage_Core_Model_App::ADMIN_STORE_ID
                        && !isset($priceTypeValueByStore[$store][$productId])
                        && isset($priceTypeValueByStore[Mage_Core_Model_App::ADMIN_STORE_ID][$productId])
                    ) {
                        //Use Default Value
                        $priceType = $priceTypeValueByStore[Mage_Core_Model_App::ADMIN_STORE_ID][$productId];
                    }
                    $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);


                    if (!empty($newPrice)) {
                        Mage::helper('zolagocatalog/pricetype')->_logQueue("New price {$priceType}: {$newPrice}");

                        $margin = (isset($priceMarginValuesByStore[$store]) && isset($priceMarginValuesByStore[$store][$productId])) ? $priceMarginValuesByStore[$store][$productId] : 0;
                        if ($store <> Mage_Core_Model_App::ADMIN_STORE_ID
                            && !isset($priceMarginValuesByStore[$store][$productId])
                            && isset($priceMarginValuesByStore[Mage_Core_Model_App::ADMIN_STORE_ID][$productId])
                        ) {
                            //Use Default Value
                            $margin = $priceMarginValuesByStore[Mage_Core_Model_App::ADMIN_STORE_ID][$productId];
                        }
                        Mage::helper('zolagocatalog/pricetype')->_logQueue("Margin {$priceType}: {$margin}%");

                        $newPriceWithMargin = $newPrice + $newPrice * ((int)$margin / 100);

                        Mage::helper('zolagocatalog/pricetype')->_logQueue(
                            "New price with margin $priceType: {$newPriceWithMargin}"
                        );
                        $productAction->updateAttributesNoIndex(array($productId), array('price' => $newPriceWithMargin), $store);
                        $recalculateConfigurableIds[$productId] = $productId;
                    } else {
                        Mage::helper('zolagocatalog/pricetype')->_logQueue("Converter result is empty, price not changed");
                    }
                }
            }
        }

        Mage::helper('zolagocatalog/pricetype')->_logQueue( "Reindex");

        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds(array_keys($ids));

        $indexers = array(
            'source'  => Mage::getResourceModel('catalog/product_indexer_eav_source'),
            'decimal' => Mage::getResourceModel('catalog/product_indexer_eav_decimal'),
        );
        foreach ($indexers as $indexer) {
            /** @var $indexer Mage_Catalog_Model_Resource_Product_Indexer_Eav_Abstract */
            $indexer->reindexEntities($ids);
        }
        if (Mage::helper('catalog/category_flat')->isEnabled()) {
            $fI = new Mage_Catalog_Model_Resource_Product_Flat_Indexer();
            $entityTypeID = Mage::getModel('catalog/product')->getResource()->getTypeId();
            $attribute = Mage::getModel('eav/entity_attribute')->loadByCode($entityTypeID, 'price');
            foreach ($stores as $storesId) {
                $fI->updateAttribute($attribute, $storesId, $ids);
            }
        }

        //zolago_catalog_after_update_price_type
        Mage::dispatchEvent(
            "zolago_catalog_after_update_price_type",
            array(
                 "product_ids" => array_keys($recalculateConfigurableIds)
            )
        );

        if(!empty($recalculateConfigurableIds)){
            Mage::helper('zolagocatalog/pricetype')->_logQueue( "Add to configurable recalculation queue");
            Zolago_Catalog_Helper_Configurable::queue(array_keys($recalculateConfigurableIds));
        }

        Mage::helper('zolagocatalog/pricetype')->_logQueue( "End");


    }
}