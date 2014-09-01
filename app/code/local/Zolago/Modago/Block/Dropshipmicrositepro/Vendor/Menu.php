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

        $_vendor = Mage::helper('umicrosite')->getCurrentVendor();
        $rootCatId = $_vendor->getRootCategory();
        $rootCatId = $rootCatId[1];
        if(empty($rootCatId)) {
            $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        }
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        return Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2);
    }

    /**
     * Returns main vendor categories for menu for mobile
     *
     * @return array
     */
    public function getMainVendorCategoriesMobile()
    {
        return $this->getMainVendorCategories();
    }
} 