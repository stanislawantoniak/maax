<?php

/**
 * Class Zolago_Catalog_Block_Vendor_Product_Preview
 */
class Zolago_Catalog_Block_Vendor_Product_Preview extends Mage_Core_Block_Template
{
    const ID_TO_REPLACE = '###ID###';

    public function getPreviewWebsitesUrls() {
        $urls = array();
        $vendorAllowedWebsites = $this->getVendor()->getWebsitesAllowed();
        foreach(Mage::app()->getWebsites() as $website) {
            /** @var Mage_Core_Model_Website $website */
            $allowed = false;
            $login = false;
            $password = false;
            if($website->getData('is_preview_website')) {
                $allowed = true;
                $login = $website->getData('website_login');
                $password = $website->getData('website_password');
            } elseif(in_array($website->getId(), $vendorAllowedWebsites)) {
                $allowed = true;
            }
            if($allowed) {
                foreach($website->getStores() as $store) {
                    /** @var Mage_Core_Model_Store $store */
                    $url = $store->getBaseUrl();
                    $previewUrl = false;
                    if($login && $password) {
                        $previewUrlArray = explode("//",$url);
                        $previewUrlArray[1] = $login.":".$password."@".$previewUrlArray[1];
                        $previewUrl = implode("//",$previewUrlArray);
                    }
                    $urls[substr(str_replace(array('http://','https://'),"",$url),0,-1)] = (!$previewUrl ? $url : $previewUrl)."catalog/product/view/id/".self::ID_TO_REPLACE;
                }
            }
        }
        $urls = array_unique($urls);

        return $urls;
    }

    /**
     * @return Zolago_Dropship_Model_Vendor
     */
    public function getVendor()
    {
        return Mage::getModel("udropship/session")->getVendor();
    }
}