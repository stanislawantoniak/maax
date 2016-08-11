<?php

/**
 * Class Zolago_Catalog_Helper_Listing_Pagination
 */
class Zolago_Catalog_Helper_Listing_Pagination extends Zolago_Catalog_Helper_Listing_Data
{
    public function productsCountPerPage()
    {
        $store = Mage::app()->getStore();
        $paginationSection = "zolagomodago_catalog/zolagomodago_cataloglisting_pagination";
        $limit = (int)Mage::getStoreConfig("{$paginationSection}/products_per_page_desktop", $store);
        $userAgent = $this->detectUserAgentForListing();

        switch ($userAgent) {
            case self::LISTING_USER_AGENT_IPHONE:
                $limit = (int)Mage::getStoreConfig("{$paginationSection}/products_per_page_iphone", $store);
                break;
            case self::LISTING_USER_AGENT_IPAD:
                $limit = (int)Mage::getStoreConfig("{$paginationSection}/products_per_page_ipad", $store);
                break;
            case self::LISTING_USER_AGENT_MOBILE:
                $limit = (int)Mage::getStoreConfig("{$paginationSection}/products_per_page_mobile", $store);
                break;
        }
        return $limit;
    }

}