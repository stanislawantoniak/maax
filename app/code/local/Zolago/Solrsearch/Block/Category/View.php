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

            /*  @var $lpBlock Zolago_Catalog_Block_Campaign_LandingPage */
            $lpBlock = Mage::getBlockSingleton('zolagocatalog/campaign_landingPage');
            $lpData = $lpBlock->getData('campaign_landing_page');
            $lpData = (array)$lpData;
            if(!empty($lpData)){
                if(isset($lpData["name_customer"]) && !empty($lpData["name_customer"])){
                    $headBlock->setDescription($lpData["name_customer"] . " - " . Mage::app()->getStore()->getName());
                }
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

        /*  @var $lpBlock Zolago_Catalog_Block_Campaign_LandingPage */
        $lpBlock = Mage::getBlockSingleton('zolagocatalog/campaign_landingPage');
        $lpData = $lpBlock->getData('campaign_landing_page');
        $lpData = (array)$lpData;

        if(!empty($lpData)){
            $res = false;
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

    public function getSidebarWrapper()
    {
        $categoryId = $this->getCurrentCategory()->getId();
        $name = "sidebar-c{$categoryId}-wrapper";
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();

        if ($vendor) {
            $vendorId = $vendor->getVendorId();
            $name = "sidebar-c{$categoryId}-v{$vendorId}-wrapper";
        }

        return $this->getLayout()->createBlock('cms/block')->setBlockId($name)->toHtml();
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
