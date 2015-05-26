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
                ->setTemplate('page/1column.phtml');

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
        return  $this->getChildHtml('searchfaces') . $this->getChildHtml('solrsearch_product_list');
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
