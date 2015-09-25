<?php
/**
 * @method Zolago_Turpentine_Model_Varnish_Admin _getVarnishAdmin()
 */
class Zolago_Turpentine_Model_Observer_Ban extends Nexcessnet_Turpentine_Model_Observer_Ban
{
    /**
     * @param $eventObject
     */
    public function banMultiProductPageCache( $eventObject ) {

        /** @var Nexcessnet_Turpentine_Helper_Varnish $helperVarnish */
        $helperVarnish = Mage::helper( 'turpentine/varnish' );

        if( $helperVarnish->getVarnishEnabled() ) {
            /** @var Zolago_Turpentine_Helper_Ban $banHelper */
            /** @var Nexcessnet_Turpentine_Helper_Cron $cronHelper */
            $banHelper = Mage::helper( 'turpentine/ban' );
            $cronHelper = Mage::helper( 'turpentine/cron' );
            $products = $eventObject->getProducts();
            $productIds = $eventObject->getProductIds();

            $idsForBan = array();
            if (!empty($productIds)) {
                $idsForBan = $productIds;
            } else {
                foreach ($products as $product) {
                    $idsForBan[] = $product->getId();
                }
            }

            $urlPatterns = $banHelper->getMultiProductBanRegex( $idsForBan ); //return array [0]regex [1]heating uri
            $results = $this->_getVarnishAdmin()->flushMultiUrl($urlPatterns['regex']);

            if( $this->_checkMultiResults( $results ) && $cronHelper->getCrawlerEnabled() ) {

                $origStore = Mage::app()->getStore();
                $urls = array();
                foreach( Mage::app()->getStores() as $storeId => $store ) {
                    Mage::app()->setCurrentStore( $store );
                    $baseUrl = rtrim($store->getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK ), '/');
                    foreach ($urlPatterns['heating'] as $uri) {
                        $urls[] = "$baseUrl/$uri"; //todo urls for vendors
                    }
                }
                $urls = array_flip(array_flip($urls));
                Mage::app()->setCurrentStore( $origStore );

                $cronHelper->addUrlsToCrawlerQueue( $urls );
            }
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
}