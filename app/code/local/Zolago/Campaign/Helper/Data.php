<?php

class Zolago_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @param $string
     * @return string
     */
    function createCampaignSlug($string)
    {
        //1.Create url from name
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', Mage::helper('catalog/product_url')->format($string));
        $urlKey = strtolower($urlKey);
        $slug = trim($urlKey, '-');

        //2. Check if slug exist among the campaigns url_key
        $collection = Mage::getResourceModel('zolagocampaign/campaign_collection')
            ->addFieldToFilter('url_key', $string);
        //$collection->printLogQuery(true);
        $slugExist = $collection->getFirstItem()->getUrlKey();

        //2. Check if slug exist among the URL Rewrite
        $oUrlRewriteCollection = Mage::getModel('core/url_rewrite')
            ->getCollection()
            ->addFieldToFilter('target_path', $string . '.html')
        ->printLogQuery(true);;

        if ($slugExist || count($oUrlRewriteCollection) > 0) {
            $slug = $slug . '-1.html';
        } else {
            $slug = $slug . '.html';
        }

        return $slug;
    }

    public function getBannerTypesSlots()
    {
        return Mage::getSingleton('zolagobanner/banner_type')->toOptionHash();
    }


}