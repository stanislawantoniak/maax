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
     * @todo implement full login
     * @return array
     */
    public function getMainCategories()
    {
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        return self::getCategoriesTree($categories, 1, 3);
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
        return self::getCategoriesTree($categories, 1, 2);
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
        return self::getCategoriesTree($categories, 1, 2);
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
            $subCategories = self::getSubCategories($currentCategory->getId());
        }
        return $subCategories;
    }

    /**
     * @param      $categories
     * @param int  $level
     * @param bool $span
     *
     * @return array
     */
    protected static function  getCategoriesTree($categories, $level = 1, $span = false)
    {
        $tree = array();

        foreach ($categories as $category) {
            $cat = Mage::getModel('catalog/category')->load($category->getId());

            $tree[$category->getId()] = array(
                'name'           => $category->getName(),
                'url'            => rtrim(Mage::getUrl($cat->getUrlPath()), "/"),
                'category_id'    => $category->getId(),
                'level'          => $level,
                'products_count' => $cat->getProductCount()
            );
            if ($level == 1) {
                $tree[$category->getId()]['image'] = $cat->getImage();
            }
            if ($span && $level >= $span) {
                continue;
            }
            if ($category->hasChildren()) {
                $children = Mage::getModel('catalog/category')->getCategories($category->getId());
                $tree[$category->getId()]['has_dropdown'] = self::getCategoriesTree($children, $level + 1, $span);
            }
        }

        return $tree;
    }

    /**
     * @param $parentId
     *
     * @return array
     */
    protected static function getSubCategories($parentId)
    {
        $children = Mage::getModel('catalog/category')->getCategories($parentId);
        $subCategories = array();
        if (!empty($children)) {
            foreach ($children as $cat) {
                $subCategories[$cat->getId()] = array(
                    'url'   => $cat->getRequestPath(),
                    'label' => $cat->getName()
                );
            }
        }
        return $subCategories;
    }
} 