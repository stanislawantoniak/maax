<?php
class Zolago_Solrsearch_Block_Category_View extends Mage_Core_Block_Template {

    public function _construct() {
        parent::_construct();

        $this->setTemplate("zolagosolrsearch/category/view.phtml");
    }
	
	protected function _prepareLayout() {
        parent::_prepareLayout();

		/** @var Zolago_Dropship_Model_Vendor $vendor */
		$vendor = Mage::helper('umicrosite')->getCurrentVendor();

        if ($headBlock = $this->getLayout()->getBlock('head')) {
			/** @var Zolago_Catalog_Model_Category $category */
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
				$noVendor = false;
				if ($vendor && $vendor->getId()) {
					$root = $vendor->getRootCategory();
					$websiteId = (int)Mage::app()->getWebsite()->getId();
					if (isset($root[$websiteId]) && empty($root[$websiteId])) {
						// if empty this means vendor have root category same as gallery
						// so canonical should be gallery link
						$noVendor = true;
					}
				}
				$headBlock->addLinkRel('canonical', $category->getCanonicalUrl($noVendor));
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
            $lambda = function ($params) {
                return $params['scope']->renderSidebarWrapper($params['category'], $params['vendor']);
            };
            $cacheKey = $name . '_auto_render_' . Mage::app()->getStore()->getId();
            $blockHtml = Mage::helper('zolagocommon')->getCache(
                $cacheKey
                , self::CACHE_GROUP
                , $lambda
                , array('category' => $category, 'vendor' => $vendor, 'scope' => $this)
            );
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
        $blockHtml .= '<div id="sidebar" class="clearfix">';
        $blockHtml .= '<div class="sidebar">';

        $blockHtml .= '<div class="section clearfix hidden-xs">';
        $blockHtml .= '<h3 class="open no-pointer"><strong>' . $category->getLongName() . '</strong></h3>';
        $blockHtml .= '<div class="content content-cms bigger-left">';
        $blockHtml .= '<dl class="no-margin">';
        foreach ($categories as $cat) {
            $blockHtml .= '<dd><a href="' . $cat["url"] . '" class="simple">' . $cat["name"] . '</a></dd>';

        }
        $blockHtml .= '</dl>';

        $blockHtml .= '</div>';
        $blockHtml .= '</div>';
        $blockHtml .= '</div>';
        $blockHtml .= '</div>';

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
