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

        //$websites = array();
        $listUpdatedProducts = array();


        foreach ($collection as $colItem) {
            $productId = $colItem->getProductId();
            $listUpdatedProducts[$productId] = $productId;
        }
        unset($productId);

        Mage::helper('zolagocatalog/pricetype')->_logQueue($listUpdatedProducts);

        $types = array(
            851 => 'A',
            852 => 'B',
            853 => 'C',
            854 => 'Z'
        );

        try {
            $converter = Mage::getModel('zolagoconverter/client');
        } catch (Exception $e) {
            Mage::throwException("DHL client is unavailable");
            return;
        }
        $vendorExternalId = 4;
        $productAction = Mage::getSingleton('catalog/product_action');
        foreach($listUpdatedProducts as $productId){

            Mage::helper('zolagocatalog/pricetype')->_logQueue("Product {$productId}");
            $product = Mage::getModel('catalog/product')->load($productId);

            if($product){
                $vendorSku = $product->getSkuv();
                $priceType = $types[$product->getConverterPriceType()];

                Mage::helper('zolagocatalog/pricetype')->_logQueue("priceType {$priceType}");

                //$newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);
                $newPrice = 5000;

                if (!empty($newPrice)) {
                    Mage::helper('zolagocatalog/pricetype')->_logQueue("New price {$priceType}: {$newPrice}");

                    $margin = (int)$product->getPriceMargin();

                    Mage::helper('zolagocatalog/pricetype')->_logQueue("Margin {$priceType}: {$margin}%");

                    $newPriceWithMargin = $newPrice + $newPrice * ((int)$margin / 100);

                    Mage::helper('zolagocatalog/pricetype')->_logQueue(
                        "New price with margin $priceType: {$newPriceWithMargin}"
                    );
                    $productAction->updateAttributesNoIndex(
                        array($productId), array('price' => $newPriceWithMargin), 0
                    );
                    $productAction->updateAttributesNoIndex(
                        array($productId), array('price' => $newPriceWithMargin), 1
                    );
                    $productAction->updateAttributesNoIndex(
                        array($productId), array('price' => $newPriceWithMargin), 2
                    );
                } else {
                    Mage::helper('zolagocatalog/pricetype')->_logQueue("Converter result is empty, price not changed");
                }
            }

        }
        unset($productId);

        Mage::helper('zolagocatalog/pricetype')->_logQueue( "Reindex");

        Mage::getResourceSingleton('catalog/product_indexer_price')
            ->reindexProductIds(array_keys($listUpdatedProducts));

        Mage::helper('zolagocatalog/pricetype')->_logQueue( "End");


    }
}