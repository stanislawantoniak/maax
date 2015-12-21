<?php
/**
 * Created by PhpStorm.
 * User: Andrzej Spólnicki
 * Date: 27.08.14
 * Time: 15:46
 */

class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Menu extends Mage_Core_Block_Template {

    /**
     * Returns main vendor categories for menu for desktop
     *
     * @return array
     */
    public function getMainVendorCategories()
    {

        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        $websiteId = Mage::app()->getWebsite()->getId();

        $rootCatId = $this->helper('zolagodropshipmicrosite')->getVendorRootCategory($vendor, $websiteId);
        if(empty($rootCatId)) {
            $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        }
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        return Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2, TRUE,TRUE);
    }

    /**
     * Returns main vendor categories for menu for mobile
     *$vendor
     * @return array
     */
    public function getMainVendorCategoriesMobile()
    {
        return $this->getMainVendorCategories();
    }
} 