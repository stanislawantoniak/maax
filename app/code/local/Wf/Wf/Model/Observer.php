<?php
/**
 * cache on frontend carousel
 */

class Wf_Wf_Model_Observer {
    public function banFeaturedProductsCache(Varien_Event_Observer $eventObject) {
        /* czyścimy dowolne produkty, jeśli będzie trzeba wybiórczo - odkomentować i dopisać resztę */
/*        $products = $eventObject->getProducts();
        $productIds = $eventObject->getProductIds();

        $idsForBan = array();
        if (!empty($productIds)) {
            $idsForBan = $productIds;
        } else {
            foreach ($products as $product) {
                $idsForBan[] = $product->getId();
            }
        }
        */
        $stores = Mage::app()->getStores(); // remove from all store
        foreach ($stores as $store) {
            Mage::app()->setCurrentStore($store);
            $key = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId('wf_featured_products')->getCacheKey();
            Mage::app()->removeCache($key);
        }
    }
}