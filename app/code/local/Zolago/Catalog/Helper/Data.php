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
     * Clean product name:
     * - replace multiple space with single one
     * - trim
     * - escape html
     *
     * @param $text
     * @return string
     */
    public static function cleanProductName($text) {
        $text = preg_replace('/ +/', ' ', $text);
        $text = trim($text);
        $text = Mage::helper('core')->escapeHtml($text);
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
        $parentCategoryPath = Mage::getUrl('/');;
        $currentCategory = $category;
        if ($vendor = Mage::helper('umicrosite')->getCurrentVendor()) {            
            $rootCategory = $vendor->getRootCategory();
        } else {
            $rootCategory = Mage::app()->getStore()->getRootCategoryId();        
        }
        if (!empty($currentCategory)) {
            $campaign = $currentCategory->getCurrentCampaign();
            if ($campaign && ($campaign->getLandingPageCategory() == $category->getId())) {
                if ($currentCategory->getId() != $rootCategory) {
                    $parentCategoryPath = $category->getUrlContext(false,false); // the same category without campaign
                }
            } else {
                if($currentCategory->getId() != $rootCategory) {
                    $currentCategoryParent = $currentCategory->getParentCategory();  
                    $parentCategoryPath = $currentCategoryParent->getUrlContext(false);                  
                }
            }
        }
        return $parentCategoryPath;
     }

	/**
	 * @param Zolago_Dropship_Model_Vendor $vendor
	 * @return float
	 */
	public function getAutomaticStrikeoutPricePercent(Zolago_Dropship_Model_Vendor $vendor) {
		$percent = $vPercent = $vendor->getAutomaticStrikeoutPricePercent();
		if (empty($vPercent)) {
			$percent = Mage::getStoreConfig('catalog/price/automatic_strikeout_price_percent');
		}
		return (float)str_replace(",", ".", $percent);
	}
    public function getStoreDeliveryHeadline($product, $vendor = null,$inventory = null) {
        $storeDeliveryHeadline = "";
        /* @var $product Zolago_Catalog_Model_Product */
        if (is_null($inventory)) {
            $inventory = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        }

        $backorders = (int)$inventory->getBackorders();        
        $qty = (int)$inventory->getQty();

        $backordersInfo = $product->getBackordersInfo();
        if(!$product->isSalable()){
            return $storeDeliveryHeadline;
        }

        if(
            $inventory->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE
        ){
            //fo bundle always show from product
            return $backordersInfo;
        }
        if (
            $backorders > 0
            && $qty <= 0
        ) {
            $storeDeliveryHeadline = $backordersInfo;
        } else {
            if (is_null($vendor)) {
                $vendor = Mage::helper('udropship')->getVendor($product->getUdropshipVendor());
            }
            $storeDeliveryHeadline = $vendor->getStoreDeliveryHeadline();
        }
        return $storeDeliveryHeadline;

    }	
}