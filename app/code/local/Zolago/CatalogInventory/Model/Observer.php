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
        $productId = $product->getId();
        $productBefore = Mage::getModel('catalog/product')->load($productId);

        $attributesAffected = false;

        //Price should be switched/saved/calculated only if are different
        if ($productBefore->getConverterPriceType() !== $product->getConverterPriceType()) {
            $attributesAffected = true;
        }

        if ((int)$productBefore->getPriceMargin() !== (int)$product->getPriceMargin()) {
            $attributesAffected = true;
        }


        if ($attributesAffected) {
            Mage::helper('zolagocatalog/pricetype')->_log("{$productId} Converter price type attributes affected");
            //Add to queue
            Zolago_Catalog_Helper_Pricetype::queueProduct($productId);
            //------Add to queue
        }
    }
}