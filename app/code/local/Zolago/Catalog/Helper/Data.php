<?php
/**
 * Class Zolago_Catalog_Helper_Data
 */
class Zolago_Catalog_Helper_Data extends Mage_Core_Helper_Abstract {
    const ADDITIONAL_ATTRIBUTES_GROUP	= 'Additional columns';
    const SPECIAL_LABELS_OLD_DELIMITER	= ':';
    const SPECIAL_LABELS_NEW_DELIMITER	= ' | ';

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


    function secureInvisibleContent( $text )
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


}