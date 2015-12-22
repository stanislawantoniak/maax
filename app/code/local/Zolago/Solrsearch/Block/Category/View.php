<?php
class Zolago_Solrsearch_Block_Category_View extends Mage_Core_Block_Template {

    public function _construct() {
        parent::_construct();

        $this->setTemplate("zolagosolrsearch/category/view.phtml");
    }
	
	protected function _prepareLayout() {
        parent::_prepareLayout();

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $category = $this->getCurrentCategory();
            if ($title = $category->getMetaTitle()) {
                $headBlock->setTitle($title);
            }
            if ($description = $category->getMetaDescription()) {
                $headBlock->setDescription($description);
            }
            if ($keywords = $category->getMetaKeywords()) {
                $headBlock->setKeywords($keywords);
            }
            if ($this->helper('catalog/category')->canUseCanonicalTag()) {
                $headBlock->addLinkRel('canonical', $category->getCanonicalUrl());
            }

            /*rewrite gh_url_rewrite*/
            $rewriteData = Mage::helper("ghrewrite")->getCategoryRewriteData();
            if (!empty($rewriteData)) {
                if (isset($rewriteData['title']) && !empty($rewriteData['title'])) {
                    $headBlock->setTitle($rewriteData['title']);
                }
                if (isset($rewriteData['meta_description']) && !empty($rewriteData['meta_description'])) {
                    $headBlock->setDescription($rewriteData['meta_description']);
                }
                if (isset($rewriteData['meta_keywords']) && !empty($rewriteData['meta_keywords'])) {
                    $headBlock->setKeywords($rewriteData['meta_keywords']);
                }
            }
            /*rewrite gh_url_rewrite*/

            /* @var $campaign Zolago_Campaign_Model_Campaign */
            $campaign = $category->getCurrentCampaign();

            if($campaign){
                $headBlock->setTitle($campaign->getNameCustomer() . " - " . Mage::app()->getStore()->getName());
            }
        }

        if($this->isContentMode()) {
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('content-mode');
        } else {
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('not-content-mode');
        }

		if($this->isContentMode()){
			$this->getLayout()->getBlock('content')->
					//unsetChild('solrsearch_result_title')->
					unsetChild('solrsearch_product_list_active')->
					unsetChild('solrsearch_product_list_toolbar');
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('node-type-main_categories')
                ->addBodyClass('is-content-mode')
                //->setTemplate('page/1column.phtml')
            ;

			$this->getLayout()->getBlock('before_body_end')->unsetChild('searchfaces');


		} elseif(!$this->isMixedMode()) {
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('node-type-list')
                ->addBodyClass('filter-sidebar');
        }

		if($this->isMixedMode()) {
			$this->getLayout()
				->getBlock('root')
				->addBodyClass('filter-sidebar');
		}

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if($vendor && $vendor->getId()) {
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('vendor-top-bottom-header');
        }

		if($this->helper('zolagocommon')->isGoogleBot()) {
			$this->getLayout()->getBlock('root')->addBodyClass('googlebot');
		}

		return parent::_prepareLayout();
	}
	
    public function isContentMode() {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PAGE) {
            $res = true;
        }
        return $res;
    }
    public function isMixedMode() {
        return $this->getCurrentCategory()->getDisplayMode()==Mage_Catalog_Model_Category::DM_MIXED;
    }

    public function getCurrentCategory() {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');

    }
    public function getCmsBlockHtml() {
        if (!$this->getData('cms_block_html')) {
            $html = $this->getLayout()->createBlock('cms/block')
                    ->setBlockId($this->getCurrentCategory()->getLandingPage())
                    ->toHtml();
            $this->setData('cms_block_html', $html);
        }
        return $this->getData('cms_block_html');

    }

    /**
     * @return string
     */
    public function getSidebarWrapper()
    {
        $category = $this->getCurrentCategory();
        $categoryId = $category->getId();
        $name = "sidebar-c{$categoryId}-wrapper";
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        if ($vendor) {
            $vendorId = $vendor->getVendorId();
            $name = "sidebar-c{$categoryId}-v{$vendorId}-wrapper";
        }
        $block = $this->getLayout()->createBlock('cms/block')->setBlockId($name);
        $blockId = Mage::getModel('cms/block')->load($name)->getId();

        if ($blockId) {
            $blockHtml = $block->toHtml();
        } else {
            //Render automatically
            $blockHtml = $this->renderSidebarWrapper($category, $vendor);
        }
        return $blockHtml;
    }

    /**
     * @param $category
     * @param $vendor
     * @return string
     */
    public function renderSidebarWrapper($category, $vendor)
    {
        $blockHtml = '';
        $categories = $this->getRenderMenuCategories($category, $vendor);

        if (empty($categories)) {
            return $blockHtml;
        }
        $blockHtml .= '<div class="sidebar"><div class="section clearfix hidden-xs">';
        $blockHtml .= '<h3 class="open">' . $category->getLongName() . '</h3>';
        $blockHtml .= '<ul class="nav nav-pills nav-stacked">';
        foreach ($categories as $cat) {
            $blockHtml .= '<li><a href="' . $cat["url"] . '" class="simple">' . $cat["name"] . '</a></li>';

        }
        $blockHtml .= '</ul>';
        $blockHtml .= '</div></div>';

        return $blockHtml;
    }

    /**
     * @param $category
     * @param $vendor
     * @return mixed
     */
    public function getRenderMenuCategories($category, $vendor){
        if(!$this->getData("sidebar_menu_categories")){
            $categories = Mage::getModel('catalog/category')->getCategories($category->getId());
            if($vendor){
                $menu = Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 1, true, $vendor);
            } else {
                $menu = Mage::helper('zolagomodago')->getCategoriesTree($categories, 1, 1, false);
            }
            $this->setData("sidebar_menu_categories",$menu);
        }
        return $this->getData("sidebar_menu_categories");
    }

    public function getProductListHtml() {
        return  $this->getChildHtml('searchfaces') . $this->getChildHtml('categoryrewrite') . $this->getChildHtml('solrsearch_product_list');
    }

    public function getDesktopVendorHeaderPanel()
    {
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if($vendor && $vendor->getId()) {
            return $this
                ->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('top-bottom-header-desktop-v-' . $vendor['vendor_id'])
                ->toHtml();
        } else {
            return '';//no current vendor
        }

    }

    public function getMobileVendorHeaderPanel()
    {
        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if($vendor && $vendor->getId()) {
            return $this
                ->getLayout()
                ->createBlock('cms/block')
                ->setBlockId('top-bottom-header-mobile-v-' . $vendor['vendor_id'])
                ->toHtml();
        } else {
            return '';//no current vendor
        }
    }

    public function getMobileMenu() {

        return $this->getChildHtml('mobile-menu');
    }
}
