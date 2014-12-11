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
        //Mage::helper('zolagocatalog/pricetype')->_logQueue( "Start process queue");
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
        if(!empty($priceTypeValues)) {
            foreach($priceTypeValues as $priceTypeValue) {
                $priceTypeValueByStore[$priceTypeValue['store']][$priceTypeValue['product_id']] = $priceTypeValue['converter_price_type_label'];
            }
            unset($priceTypeValue);
        }

        $priceMarginValues = $queueModel->getPriceMarginValues($ids);

        //reformat
        $priceMarginValuesByStore = array();
        if(!empty($priceMarginValues)) {
            foreach($priceMarginValues as $priceMarginValue) {
                $priceMarginValuesByStore[$priceMarginValue['store']][$priceMarginValue['product_id']] = $priceMarginValue['price_margin'];
            }
            unset($priceMarginValue);
        }


        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("Converter is unavailable");
            //Mage::helper('zolagocatalog/pricetype')->_logQueue("Converter is unavailable: check credentials");
            return;
        }
        $registry = array();
        $productAction = Mage::getSingleton('catalog/product_action');
        if (!empty($skuvs)) {

            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                $stores[] = $_storeId;
            }
            $skuvs_count = count($skuvs);
            foreach ($skuvs as $count => $productData) {
                $productId = $productData['product_id'];
                $parentId = $productData['parent'];
                $vendorSku = $productData['skuv'];

                $vendorExternalId = $productData['vendor'];
                if (!$vendorExternalId) {
                    continue;
                }

                //Mage::helper('zolagocatalog/pricetype')->_logQueue("Product {$productId}");
                foreach($stores as $store) {
                    $priceType = (isset($priceTypeValueByStore[$store]) && isset($priceTypeValueByStore[$store][$parentId])) ? $priceTypeValueByStore[$store][$parentId] : 0;
                    if ($priceType == '0') {
                        continue;
                    }
                    if (!isset($registry[$vendorExternalId][$vendorSku][$priceType])) {
                        $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);
                        $registry[$vendorExternalId][$vendorSku][$priceType] = (empty($newPrice)? '0':$newPrice);
                    } else {
                        $newPrice = $registry[$vendorExternalId][$vendorSku][$priceType];
                    }
                    if (!empty($newPrice)) {
                        //Mage::helper('zolagocatalog/pricetype')->_logQueue("New price {$priceType}: {$newPrice}");

                        $margin = (isset($priceMarginValuesByStore[$store]) && isset($priceMarginValuesByStore[$store][$parentId])) ? $priceMarginValuesByStore[$store][$parentId] : 0;
                        //Mage::helper('zolagocatalog/pricetype')->_logQueue("Margin {$priceType}: {$margin}%");

                        $newPriceWithMargin = $newPrice + $newPrice * ((int)$margin / 100);

                        //Mage::helper('zolagocatalog/pricetype')->_logQueue("New price with margin $priceType: {$newPriceWithMargin}");
                        $productAction->updateAttributesNoIndex(array($productId), array('price' => $newPriceWithMargin), $store);
                        $recalculateConfigurableIds[$productId] = $productId;
                    } else {
                        //Mage::helper('zolagocatalog/pricetype')->_logQueue("Converter result is empty, price not changed");
                    }
                }
            }
        }
        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds(array_keys($recalculateConfigurableIds));

        if(!empty($recalculateConfigurableIds)) {
            //Mage::helper('zolagocatalog/pricetype')->_logQueue( "Add to configurable recalculation queue");
            Zolago_Catalog_Helper_Configurable::queue(array_keys($recalculateConfigurableIds));
        }


    }
}