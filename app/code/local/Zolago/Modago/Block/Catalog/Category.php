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
        $rootCatId  = Mage::app()->getStore()->getRootCategoryId();
        /** @var Zolago_Catalog_Model_Category $ccModel */
        $ccModel    = Mage::getModel('catalog/category');
        /** @var Varien_Data_Tree_Node_Collection $categories */
        $categories = $ccModel->getCategories($rootCatId);

        $catTree = array();
        $store = Mage::app()->getStore();        
        if ($virtual = trim($store->getVirtualRootCategory())) {
            $catTree[-1] = array (
                'name' => $virtual,
		'url'         => '#',
                'category_id' => -1,
		'dropdown'    => $this->getLayout()->createBlock('cms/block')->setBlockId("navigation-dropdown-c--1")->toHtml(),
                'ids'         => implode(',', $ccModel->getResource()->getChildren($ccModel->load($rootCatId), true))

            );
        } else {
            foreach ($categories as $cat) {
            /** @var Varien_Data_Tree_Node $cat */
                $catId   = (int)$cat->getId();
                $catTree[$catId] = array(
				'name'        => $cat->getName(),
				'url'         => rtrim(Mage::getUrl($cat->getRequestPath()), "/"),
				'category_id' => $catId,
				'dropdown'    => $this->getLayout()->createBlock('cms/block')->setBlockId("navigation-dropdown-c-{$catId}")->toHtml(),
                    'ids'         => implode(',', $ccModel->getResource()->getChildren($ccModel->load($catId), true))
		    	);
            }
        }
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
        $categories = $this->getMenuMainCategories();
        $store = Mage::app()->getStore();        
        if ($virtual = trim($store->getVirtualRootCategory())) {
            $tmp = array(
                'name' => $virtual,
                'url' => '#',
                'category_id' => -1,
                'level' => 1,
                'solr_product_count' => 1,
                'image' => '',
                'has_dropdown' => $categories
            );
            $categories = array($tmp);
        }
        return $categories;
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
        $category = Mage::registry('current_category');
        return Mage::helper('zolagocatalog')->getMoveUpUrl($category);
    }

    /**
     * Return array of mobile menu in main category page.
     *
     * @return array
     */
    public function getCategoryCollection()
    {
        $subCategories = array();
        /** @var Zolago_Catalog_Model_Category $currentCategory */
        $currentCategory = Mage::registry('current_category');
        if(!empty($currentCategory)) {
            /** @var Zolago_Modago_Helper_Data $zmHelper */
            $zmHelper = Mage::helper('zolagomodago');
            /** @var Zolago_Catalog_Model_Category $categoryModel */
            $categoryModel = Mage::getModel('catalog/category');
            $categories = $categoryModel->getCategories($currentCategory->getId());
            $subCategories = $zmHelper->getCategoriesTree($categories, 1, 2, TRUE, TRUE);
        }
        return $subCategories;
    }
}
