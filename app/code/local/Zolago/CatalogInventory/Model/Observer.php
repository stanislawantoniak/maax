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
        if ((int)$productBefore->getConverterPriceType() !== (int)$product->getConverterPriceType())
            $attributesAffected = true;

        if ((int)$productBefore->getPriceMargin() !== (int)$product->getPriceMargin())
            $attributesAffected = true;

        if ($attributesAffected)
            Zolago_Catalog_Helper_Configurable::queueProduct($product->getId());

    }
}