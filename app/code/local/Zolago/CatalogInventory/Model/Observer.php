<?php
/**
 * @category    Zolago
 * @package     Zolago_CatalogInventory
 *
 */
class Zolago_CatalogInventory_Model_Observer
{

    public function __construct()
    {

    }

    /**
     * When added/update a product
     *
     * @param Varien_Event_Observer $observer
     */
    public function productBeforeUpdate($observer)
    {
        $product = $observer->getProduct();
        $productBefore = Mage::getModel('catalog/product')->load($product->getId());

        $attributesAffected = false;

        //Price should be switched/saved/calculated only if are different
        if ($productBefore->getConverterPriceType() !== $product->getConverterPriceType())
            $attributesAffected = true;

        if ((int)$productBefore->getPriceMargin() !== (int)$product->getPriceMargin())
            $attributesAffected = true;

        Mage::log('attributesAffected ' . $attributesAffected);
        if ($attributesAffected) {
            //Zolago_Catalog_Helper_Configurable::queueProduct($product->getId());



            //Add to queue
            $types = array(
                851 => 'A',
                852 => 'B',
                853 => 'C',
                854 => 'Z'
            );
            $converter = Mage::getModel('zolagoconverter/client');
            $vendorExternalId = 4;
            $vendorSku = $product->getSkuv();
            Mage::log("VendorSku {$vendorSku}");
            $priceType = $types[$product->getConverterPriceType()];

            $newPrice = $converter->getPrice($vendorExternalId, $vendorSku, $priceType);
            if(!empty($newPrice)){
                Mage::log('New price ' . $priceType . ": ". $newPrice);
                $margin = (int)$product->getPriceMargin();
                Mage::log('Margin ' . $priceType . ": ". $margin . '%');

                $newPriceWithMargin = $newPrice + $newPrice * ((int)$margin / 100);
                Mage::log('New price with margin ' . $priceType . ": ". $newPriceWithMargin);
                $product->setPrice($newPriceWithMargin);
            } else {
                Mage::log('Converter result is empty, price not changed');
            }
            //------Add to queue

        }

    }
}