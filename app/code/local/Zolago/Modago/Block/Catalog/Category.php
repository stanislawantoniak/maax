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

            $_query = null;

            $campaign = Mage::helper("zolagocampaign/landingPage")->getCampaign();

            if($campaign->getData("is_landing_page")){
                $landing_page_category = $campaign->getData("landing_page_category");

                if($landing_page_category !== $currentCategory->getId()){
                    $_fq = $this->getRequest()->getParam('fq');
                    $_query['fq']['campaign_info_id']    = isset($_fq['campaign_info_id'])    ? $_fq['campaign_info_id']    : null;
                    $_query['fq']['campaign_regular_id'] = isset($_fq['campaign_regular_id']) ? $_fq['campaign_regular_id'] : null;
                }
//                if($landing_page_category == $currentCategory->getId()){
//                    $urlPath = $currentCategory->getUrlPath();
//                }

            }

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





            $parentCategoryPath = !is_null($_query) ? Mage::getUrl($urlPath, array('_query'  => $_query)) : Mage::getUrl($urlPath);
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
