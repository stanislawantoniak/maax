<?php
/**
 * Author: Paweł Chyl <pawel.chyl@orba.pl>
 * Date: 09.07.2014
 */

class Zolago_Modago_Block_Catalog_Category extends Mage_Core_Block_Template
{

    /**
     * Returns main categories for dropdown menu
     *
     * @return array
     */
    public function getMainCategories()
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        return Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 3);
    }

    /**
     * Returns main categories for mobile navigation menu under black header
     *
     * @return array
     */
    public function getMainCategoriesMobile()
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        return Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2);
    }


    /**
     * Returns categories for sliding menu(hamburger menu)
     *
     * @return array
     */
    public function getMainCategoriesForSlidingMenu()
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')
            ->getCategories($rootCatId);
        return Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2);
    }

    /**
     * Returns category label for mobile menu in main category page
     *
     * @return string
     */
    public function getCategoryLabel()
    {
        $categoryLabel = '';
        $currentCategory = Mage::registry('current_category');
        if (!empty($currentCategory)) {
            $categoryLabel = $currentCategory->getName();
        }
        return $categoryLabel;
    }

    /**
     * Returns go up url for mobile version of main category page's menu.
     * @return string
     */
    public function getMoveUpUrl()
    {
        $parentCategoryPath = '/';
        $currentCategory = Mage::registry('current_category');
        if (!empty($currentCategory)) {
            $parentCategoryPath = Mage::getUrl($currentCategory->getParentCategory()->getUrlPath());
        }
        return $parentCategoryPath;
    }

    /**
     * Return array of mobile menu in main category page.
     *
     * @return array
     */
    public function getCategoryCollection()
    {
        $subCategories = array();
        $currentCategory = Mage::registry('current_category');
        if(!empty($currentCategory)){
            $subCategories = Mage::helper('zolagomodago')->getSubCategories($currentCategory->getId());
        }
        return $subCategories;
    }
} 