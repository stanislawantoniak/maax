<?php
/**
 * @method Zolago_Turpentine_Model_Varnish_Admin _getVarnishAdmin()
 */
class Zolago_Turpentine_Model_Observer_Ban extends Nexcessnet_Turpentine_Model_Observer_Ban
{
    /**
     * @param $eventObject
     */
    public function banMultiProductPageCache($eventObject)
    {Mage::log($eventObject->getProducts(), null, "XXX.log");

        if (!self::isVarnishEnabled()) {
            return;
        }
        /** @var Zolago_Turpentine_Helper_Ban $banHelper */
        /** @var Nexcessnet_Turpentine_Helper_Cron $cronHelper */
        $banHelper = Mage::helper('turpentine/ban');
        $cronHelper = Mage::helper('turpentine/cron');
        $products = $eventObject->getProducts();
        Mage::log($eventObject->getProducts(), null, "XXX1.log");
        if (!$products) {
            return;
        }

        $idsForBan = array();
        foreach ($products as $product) {
            $idsForBan[] = $product->getId();
        }
        Mage::log($idsForBan, null, "XXX3.log");
        $urlPatterns = $banHelper->getMultiProductBanRegex($idsForBan); //return array [0]regex [1]heating uri
        $results = $this->_getVarnishAdmin()->flushMultiUrl($urlPatterns['regex']);

        if ($this->_checkMultiResults($results) && $cronHelper->getCrawlerEnabled()) {

            $origStore = Mage::app()->getStore();
            $urls = array();
            foreach (Mage::app()->getStores() as $storeId => $store) {
                Mage::app()->setCurrentStore($store);
                $baseUrl = rtrim($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK), '/');
                foreach ($urlPatterns['heating'] as $uri) {
                    $urls[] = "$baseUrl/$uri"; //todo urls for vendors
                }
            }
            $urls = array_flip(array_flip($urls));
            Mage::app()->setCurrentStore($origStore);

            $cronHelper->addUrlsToCrawlerQueue($urls);
        }

    }

    /**
     * Check a results, log if result has errors
     * no errors == true
     * @param  array $results
     * @return bool
     */
    protected function _checkMultiResults( $results ) {
        $rvalue = true;
        foreach( $results as $socketName => $msgs ) {
            foreach ($msgs as $msg) {
                Mage::helper( 'turpentine/debug' )->logWarn(
                    'Error in Varnish action result for server [%s]: %s',
                    $socketName, $msg );
                $rvalue = false;
            }
        }
        return $rvalue;
    }


    /**
     * Collect products before ban:
     * gather only active and visible parents for children
     *
     * @param $productIds
     * @return Zolago_Catalog_Model_Resource_Product_Collection
     */
    public static function collectProductsBeforeBan($productIds)
    {
        /** @var Zolago_Catalog_Model_Resource_Product_Collection $coll */
        $collection = Mage::getResourceModel('zolagocatalog/product_collection');

        if (self::isVarnishEnabled()) {

            /** @var Mage_Catalog_Model_Resource_Product_Type_Configurable $modelZCPC */
            $parentIds = $modelZCPC = Mage::getResourceModel('catalog/product_type_configurable')
                ->getParentIdsByChild($productIds);

            $parentIds = array_unique($parentIds);

            $allIds = array_merge($parentIds, $productIds);

            $collection->addFieldToFilter('entity_id', array('in' => $allIds));
            $collection->addAttributeToFilter("visibility", array('neq' => Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE));
            $collection->addAttributeToFilter("status", array('eq' => Mage_Catalog_Model_Product_Status::STATUS_ENABLED));
        }
        return $collection;
    }


    /**
     * Check if Varnish Enabled
     * @return bool
     */
    public static function isVarnishEnabled()
    {
        /** @var Nexcessnet_Turpentine_Helper_Varnish $helperVarnish */
        return $helperVarnish = Mage::helper('turpentine/varnish')->getVarnishEnabled();
    }
}