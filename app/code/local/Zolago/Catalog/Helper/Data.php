<?php
/**
 * Class Zolago_Catalog_Helper_Data
 */
class Zolago_Catalog_Helper_Data extends Mage_Core_Helper_Abstract {
    const ADDITIONAL_ATTRIBUTES_GROUP	= 'Additional columns';
    const SPECIAL_LABELS_OLD_DELIMITER	= ':';
    const SPECIAL_LABELS_NEW_DELIMITER	= ' | ';

    /**
     * Logs a message to /var/log/zolagocatalog.log
     *
     * @param string $message
     */
    public function log($message = '') {
        Mage::log($message, null, 'zolagocatalog.log');
    }

    /**
     * Converts timestamp to GMT date
     *
     * @param int $time
     * @return string
     */
    public function timestampToGmtDate($time) {
        return gmdate('D, d M Y H:i:s', $time) . ' GMT';
    }
    /**
     * get id-sku associated array
     * @return array
     */
    public static function getIdSkuAssoc()
    {
        $posResourceModel = Mage::getResourceModel('zolagopos/pos');
        $skuAssoc = $posResourceModel->getIdSkuAssoc();
        return $skuAssoc;
    }

    /**
     * get sku-id associated array
     *
     * @param array $skus
     *
     * @return array
     */
    public static function getSkuAssoc($skus = array())
    {
        /* @var $posResourceModel Zolago_Pos_Model_Resource_Pos */
        $posResourceModel = Mage::getResourceModel('zolagopos/pos');
        $skuAssoc = $posResourceModel->getSkuAssoc($skus);
        return $skuAssoc;
    }


    /**
     * @param $websiteIds
     * @return array
     */
    public function getStoresForWebsites($websiteIds)
    {
        if(empty($websiteIds)) {
            return;
        }
        $stores = array();
        $storesCollection = Mage::getModel('core/store')->getCollection();
        $storesCollection->addFieldToFilter('website_id', array('in', $websiteIds));

        foreach ($storesCollection as $storesCollectionI) {
            $storeId = $storesCollectionI->getStoreId();
            $websiteId = $storesCollectionI->getWebsiteId();
            $stores[$websiteId][$storeId] = $storeId;
        }
        return $stores;
    }

    /**
     * brand id
     * @return int
     */
    public function getBrandId() {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product','manufacturer');
        return $attribute->getId();
    }


    static function secureInvisibleContent( $text )
    {
        $text = preg_replace(
            array(
                // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu'
            ),
            array(
                '', '', '', '', '', '', '', '', ''
            ),
            $text );
        return $text;
    }

    /**
     * return shorted to $n letters escaped name of product for visual purpose
     *
     * @param $name
     * @param $n
     * @return mixed|string
     */
    public function getShortProductName($name, $n, $length = 50) {
        $productName = $this->escapeHtml($name);

        if (strlen($productName) > $length) {
            $productName = substr($productName, 0, $n) . '...';
        }
        return $productName;
    }
    
    /**
     * prepare move up url for category including landing page context
     * @param Mage_Catalog_Model_Category $category
     * @return string
     */
     public function getMoveUpUrl($category) {
        $parentCategoryPath = '/';
        $currentCategory = $category;
        if (!empty($currentCategory)) {
            $currentCategoryParent = $currentCategory->getParentCategory();
            $urlPath = $currentCategoryParent->getUrlPath();
            $campaign = $currentCategoryParent->getCurrentCampaign();
            $params = array();
            if ($campaign) {
                $key = $campaign->getCampaignFilterKey();
                $params['_query']['fq'][$key][] = $campaign->getId();
            }
            $currentCategoryParentId = $currentCategoryParent->getId();

            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            if (!empty($vendor)) {
                $vendorRootCategory = $vendor->getRootCategory();

                if (!empty($vendorRootCategory)) {
                    $currentStoreId = Mage::app()->getStore()->getId();
                    $vendorRootCategoryForSite = isset($vendorRootCategory[$currentStoreId]) ? $vendorRootCategory[$currentStoreId] : false;
                    if ($vendorRootCategoryForSite) {
                        if ($vendorRootCategoryForSite == $currentCategoryParentId) {
                            $urlPath = $parentCategoryPath;
                        }
                    }
                }
            }
            $parentCategoryPath = Mage::getUrl($urlPath,$params);
        }
        return $parentCategoryPath;

     }

}