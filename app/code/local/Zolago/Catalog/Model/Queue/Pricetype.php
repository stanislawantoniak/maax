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

        $vendorExternalId = 4;
        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("DHL client is unavailable");
            return;
        }
        $productAction = Mage::getSingleton('catalog/product_action');
        if (!empty($skuvs)) {
            foreach ($skuvs as $productId => $vendorSku) {

                $stores = array(0,1,2);
                foreach($stores as $store){
                    $priceType = (isset($priceTypeValueByStore[$store]) && isset($priceTypeValueByStore[$store][$productId])) ? $priceTypeValueByStore[$store][$productId] : 0;

                    $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);


                    if (!empty($newPrice)) {
                        Mage::helper('zolagocatalog/pricetype')->_logQueue("New price {$priceType}: {$newPrice}");

                        $margin = (isset($priceMarginValuesByStore[$store]) && isset($priceMarginValuesByStore[$store][$productId])) ? $priceMarginValuesByStore[$store][$productId] : 0;

                        Mage::helper('zolagocatalog/pricetype')->_logQueue("Margin {$priceType}: {$margin}%");

                        $newPriceWithMargin = $newPrice + $newPrice * ((int)$margin / 100);

                        Mage::helper('zolagocatalog/pricetype')->_logQueue(
                            "New price with margin $priceType: {$newPriceWithMargin}"
                        );
                        $productAction->updateAttributesNoIndex(array($productId), array('price' => $newPriceWithMargin), $store);
                    } else {
                        Mage::helper('zolagocatalog/pricetype')->_logQueue("Converter result is empty, price not changed");
                    }
                }
            }
        }

        Mage::helper('zolagocatalog/pricetype')->_logQueue( "Reindex");

        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds(array_keys($ids));

        Mage::helper('zolagocatalog/pricetype')->_logQueue( "End");


    }
}