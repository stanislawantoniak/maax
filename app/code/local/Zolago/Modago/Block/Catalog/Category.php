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
		Varien_Profiler::start("todo: Load main categories");
        $rootCatId = Mage::app()->getStore()->getRootCategoryId();
        $categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
        $catTree = array();
        foreach ($categories as $categoryData) {
            $catId = (int)$categoryData->getId();
            $cat = Mage::getModel('catalog/category')->load($catId);
            $catTree[$catId] = array(
				'name' => $categoryData->getName(),
				'url' => rtrim(Mage::getUrl($cat->getUrlPath()), "/"),
				'category_id' => $catId,
				'has_dropdown' => (bool) $this->getLayout()->createBlock('cms/block')->setBlockId("navigation-dropdown-c-{$catId}")->toHtml()
			);
        }
		Varien_Profiler::stop("todo: Load main categories");
        return $catTree;
    }


	/**
	 * @return type
	 */
	public function getMenuMainCategories(){
		if(!$this->getData("menu_main_categories")){
			$rootCatId = Mage::app()->getStore()->getRootCategoryId();
			$categories = Mage::getModel('catalog/category')->getCategories($rootCatId);
			$this->setData("menu_main_categories", 
					Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 2, false));
		}
		return $this->getData("menu_main_categories");
	}
	
    /**
     * Returns main categories for mobile navigation menu under black header
     *
     * @return array
     */
    public function getMainCategoriesMobile()
    {
        return $this->getMenuMainCategories();
    }

    /**
     * Returns categories for sliding menu(hamburger menu)
     *
     * @return array
     */
    public function getMainCategoriesForSlidingMenu()
    {

        return $this->getMenuMainCategories();
    }
    /**/
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
            $currentCategoryParent = $currentCategory->getParentCategory();
            $urlPath = $currentCategoryParent->getUrlPath();
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

            $parentCategoryPath = Mage::getUrl($urlPath);
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
        if(!empty($currentCategory)) {
            $subCategories = Mage::helper('zolagomodago')->getSubCategories($currentCategory->getId());
        }
        return $subCategories;
    }
}
