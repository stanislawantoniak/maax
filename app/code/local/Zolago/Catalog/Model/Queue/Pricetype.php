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
        $msrpValues = $queueModel->getMsrpValues($ids);
        $msrpValuesByStore = array();
        if(!empty($msrpValues)) {
            foreach($msrpValues as $msrpValue) {
                $msrpValuesByStore[$msrpValue['store']][$msrpValue['product_id']] = $msrpValue['price_msrp'];
            }
            unset($msrpValue);
        }

        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException($e);
            //Mage::helper('zolagocatalog/pricetype')->_logQueue("Converter is unavailable: check credentials");
            return;
        }
        $productAction = Mage::getSingleton('catalog/product_action');
        if (!empty($skuvs)) {

            $stores = array(Mage_Core_Model_App::ADMIN_STORE_ID);
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                $_storeId = Mage::app()->getStore($_eachStoreId)->getId();
                $stores[] = $_storeId;
            }

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
                    // rebuild MSRP 
                    $msrp = (isset($msrpValuesByStore[$store]) && isset($msrpValuesByStore[$store][$parentId])) ? $msrpValuesByStore[$store][$parentId] : 1;
                    if (!$msrp) {  // 0 - from file
                        $newMsrp = $converter->getPrice($vendorExternalId,$vendorSku,Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_SOURCE);                        
                        $productAction->updateAttributesNoIndex(array($productId), array(Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_MSRP_CODE => $newMsrp), $store);
                        $recalculateConfigurableIds[$productId] = $productId;
                    }
                    $priceType = (isset($priceTypeValueByStore[$store]) && isset($priceTypeValueByStore[$store][$parentId])) ? $priceTypeValueByStore[$store][$parentId] : 0;
                    if ($priceType == '0') {
                        continue;
                    }
                    $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);
                    if (!empty($newPrice)) {
                        //Mage::helper('zolagocatalog/pricetype')->_logQueue("New price {$priceType}: {$newPrice}");

                        $margin = (isset($priceMarginValuesByStore[$store]) && isset($priceMarginValuesByStore[$store][$parentId])) ? (float)str_replace(",", ".", $priceMarginValuesByStore[$store][$parentId]) : 0;
                        //Mage::helper('zolagocatalog/pricetype')->_logQueue("Margin {$priceType}: {$margin}%");
                        //Mage::log("STORE {$store}:     SKU {$vendorSku}: price typu {$priceType} {$newPrice}, margin {$margin}", null, "priceType.log");
                        $newPriceWithMargin = $newPrice + $newPrice * ($margin / 100);
                        $newPriceWithMargin = round($newPriceWithMargin, 2);

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