<?php

class Zolago_Turpentine_Model_Observer_Ban extends Nexcessnet_Turpentine_Model_Observer_Ban
{

    public function banMultiProductPageCache( $eventObject ) {
        if( Mage::helper( 'turpentine/varnish' )->getVarnishEnabled() ) {
            $banHelper = Mage::helper( 'turpentine/ban' );
            $productIds = $eventObject->getProductIds();
            $urlPatterns = $banHelper->getMultiProductBanRegex( $productIds );

            //nie dokonczone

            $result = $this->_getVarnishAdmin()->flushUrl( $urlPattern );
            Mage::dispatchEvent( 'turpentine_ban_product_cache', $result );
            $cronHelper = Mage::helper( 'turpentine/cron' );
            if( $this->_checkResult( $result ) &&
                $cronHelper->getCrawlerEnabled() ) {
                $cronHelper->addProductToCrawlerQueue( $product );
                foreach( $banHelper->getParentProducts( $product )
                         as $parentProduct ) {
                    $cronHelper->addProductToCrawlerQueue( $parentProduct );
                }
            }
        }
    }
}