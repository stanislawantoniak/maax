<?php
/**
 * zolago wishlist observer
 */

class Zolago_Wishlist_Model_Observer {

    /**
     * increase favourite flag
     */
    public function wishlistAddAfter($obj) {
        $items = $obj->getItems();

        if (empty($items)) return;

        foreach ($items as $item) {
            $productId = $item->getProductId();
//               $attribute = Mage::getModel('eav/config')->getAttribute(Mage_Catalog_Model_Product::ENTITY,'wishlist_count');
            $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, 'wishlist_count', 0)+1;
            Mage::getSingleton('catalog/product_action')->updateAttributes(array($productId),array ('wishlist_count'=>$value),0);
        }
        return $this;
    }
    /**
     * decrease favourite flag
     */
    public function wishlistDelAfter($obj) {
        $item = $obj->getItem();

        if (empty($item)) return;

        $productId = $item->getProductId();
        $value = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, 'wishlist_count', 0)-1;
        Mage::getSingleton('catalog/product_action')->updateAttributes(array($productId),array ('wishlist_count'=>(($value>0)? $value:0)),0);
        return $this;
    }
}