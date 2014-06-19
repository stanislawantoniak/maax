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
    public function productAddUpdate($observer)
    {
        $product = $observer->getProduct();
        Zolago_Catalog_Helper_Configurable::queueProduct($product->getId());
    }
}