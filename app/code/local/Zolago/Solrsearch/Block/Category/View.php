<?php
class Zolago_Solrsearch_Block_Category_View extends Mage_Core_Block_Template {

    public function _construct() {
        parent::_construct();
        $this->setTemplate("zolagosolrsearch/category/view.phtml");
    }
	
	protected function _prepareLayout() {
		if($this->isContentMode()){
			$this->getLayout()->getBlock('content')->
					unsetChild('solrsearch_result_title')->
					unsetChild('solrsearch_product_list_active')->
					unsetChild('solrsearch_product_list_toolbar');
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('node-type-main_categories')
                ->addBodyClass('is-content-mode')
                ->setTemplate('page/1column.phtml');


		} elseif(!$this->isMixedMode()) {
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('node-type-list')
                ->addBodyClass('filter-sidebar');
        }

        /** @var Zolago_Dropship_Model_Vendor $vendor */
        $vendor = Mage::helper('umicrosite')->getCurrentVendor();
        if($vendor && $vendor->getId()) {
            $this->getLayout()
                ->getBlock('root')
                ->addBodyClass('vendor-top-bottom-header');
        }

		return parent::_prepareLayout();
	}
	
    public function isContentMode() {
        $category = $this->getCurrentCategory();
        $res = false;
        if ($category->getDisplayMode()==Mage_Catalog_Model_Category::DM_PAGE) {
            $res = true;
           /* if ($category->getIsAnchor()) {
                $state = Mage::getSingleton('catalog/layer')->getState();
                if ($state && $state->getFilters()) {
                    $res = false;
                }
            }*/
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
    public function getSidebarWrapper() {
        $category_id = $this->getCurrentCategory()->getId();
        $name = 'sidebar-c'.$category_id.'-wrapper';
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
