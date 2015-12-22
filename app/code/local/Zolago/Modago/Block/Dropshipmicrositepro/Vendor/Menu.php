<?php

/**
 * Created by PhpStorm.
 * User: Andrzej Spólnicki
 * Date: 27.08.14
 * Time: 15:46
 */
class Zolago_Modago_Block_Dropshipmicrositepro_Vendor_Menu extends Mage_Core_Block_Template
{

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
        if (empty($rootCatId)) {
            $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        }
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        return Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2, TRUE, TRUE);
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

    /**
     * @return string
     * @throws Mage_Core_Exception
     */
    public function getMainVendorCategoriesDesktop()
    {
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        $name = 'category-navigation-desktop-v-' . $vendor['vendor_id'];
        $block = $this->getLayout()->createBlock('cms/block')->setBlockId($name);
        $blockModel = Mage::getModel('cms/block')->load($name);
        $blockId = $blockModel->getId();
        $currentStoreId = Mage::app()->getStore()->getId();

        $defaultStoreId = Mage_Core_Model_App::ADMIN_STORE_ID;

        if ($blockId && ($blockModel->getIsActive() == 1)
            && (in_array($currentStoreId, $blockModel->getData("store_id")) || in_array($defaultStoreId, $blockModel->getData("store_id")))
        ) {
            $blockHtml = $block->toHtml();
        } else {
            //Render automatically
            $websiteId = Mage::app()->getWebsite()->getId();

            $rootCatId = $this->helper('zolagodropshipmicrosite')->getVendorRootCategory($vendor, $websiteId);
            if (empty($rootCatId)) {
                $rootCatId = Mage::app()->getStore()->getRootCategoryId();
            }
            $category = Mage::getModel("catalog/category")->load($rootCatId);
            $blockHtml = $this->renderVendorMenu($category, $vendor);
        }
        return $blockHtml;
    }

    /**
     * @param $category
     * @param $vendor
     * @return mixed
     */
    public function getRenderVendorMenuLeft($category, $vendor)
    {
        if (!$this->getData("vendor_menu_left")) {

            $categories = Mage::getModel('catalog/category')->getCategories($category->getId());
            $menu = Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2, true, $vendor);

            $this->setData("vendor_menu_left", $menu);
        }
        return $this->getData("vendor_menu_left");
    }

    public function renderVendorMenu($category, $vendor)
    {
        $blockHtml = '';
        $categories = $this->getRenderVendorMenuLeft($category, $vendor);

        if (empty($categories)) {
            return $blockHtml;
        }

        $blockHtml .= '<div id="sidebar" class="clearfix">';
        $blockHtml .= '<div class="sidebar">';

        foreach ($categories as $cat) {
            if(!empty($cat["has_dropdown"])){
                $blockHtml .= '<div class="section clearfix hidden-xs">';
                $blockHtml .= '<h3 class="open no-pointer"><strong>' . $cat["name"] . '</strong></h3>';
                $blockHtml .= '<div class="content content-cms bigger-left">';
                $blockHtml .= '<dl class="no-margin">';
                foreach ($cat["has_dropdown"] as $catItem) {
                    $blockHtml .= '<dd><a href="' . $catItem["url"] . '" class="simple">' . $catItem["name"] . '</a></dd>';
                }
                $blockHtml .= '</dl>';
                $blockHtml .= '</div>';
                $blockHtml .= '</div>';
            }

        }
        $blockHtml .= '</div>';
        $blockHtml .= '</div>';

        return $blockHtml;
    }
} 