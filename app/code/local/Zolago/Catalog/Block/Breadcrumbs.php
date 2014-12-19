<?php
class Zolago_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    protected $_vendor;
    protected $_breadcrumbBlock;
    protected $_path;
    protected $_rootId;

    /**
     * get product
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct() {
        if (($product = Mage::registry('current_product'))
                       && (Mage::registry('current_product') instanceof Mage_Catalog_Model_Product)) {
            return $product;
        } else {
            return 0;
        }
    }
    /**
     * return breadcrumb block
     * @return
     */

    protected function _toHtml() {
        return $this->getLayout()->getBlock('breadcrumbs')->toHtml();
    }

    /**
     * prepare breadcrumb path
     * @return array
     */
    protected function _getPath() {
        if (is_null($this->_path)) {
            $category = Mage::helper('catalog')->getCategory();
            if (!$category ||
                $category->getId() == $this->_getRootCategoryId()) {
                $category = $this->_getDefaultCategory();
            }
            if ($category) {
                $path = $this->_preparePath($category);
            } else {
                $path = array();
            }
            if ($product = $this->_getProduct()) {
                $path['product'] = array('label'=>$product->getName());
            }

            $this->_path = $path;
        }
        return $this->_path;
    }
    /**
     * get type name by vendor type
     * @param int $vendorType;
     * @return string
     */
    protected function _getVendorTypeName($vendorType) {
        $out = '';
        $helper = Mage::helper('catalog');
        switch ($vendorType) {
        case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_STANDARD:
            $out = $helper->__('Seller');
            break;
        case Zolago_Dropship_Model_Vendor::VENDOR_TYPE_BRANDSHOP:
            $out = ' '.$helper->__('Shop');
            break;
        }
        return $out;
    }

    /**
     * get actual vendor
     * @return
     */
    protected function _getVendor() {
        if (is_null($this->_vendor)) {
            $this->_vendor = 0;
            // Add vendor
            $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            if ($vendor && $vendor->getId()) {
                $this->_vendor = $vendor;
            }
        }
        return $this->_vendor;
    }

    
    /**
     * id of root category (depends from website and vendor)
     * @return int
     */
     protected function _getRootCategoryId() {
        if (is_null($this->_rootId)) {
            $vendor = $this->_getVendor();
            if ($vendor) {
                $rootId = Mage::helper('zolagodropshipmicrosite')->getVendorRootCategory($vendor,Mage::app()->getWebsite()->getId());
            } else {
                $rootId = Mage::app()->getStore()->getRootCategoryId();
            }
            $this->_rootId = $rootId;
        }
        return $this->_rootId;     
     }

    /**
     * preparing path
     * @param
     * @return array
     */
    protected function _preparePath($category) {
        $path = array();
        $rootId = $this->_getRootCategoryId();
        /* @var $category Mage_Catalog_Model_Category */
        if($category->getId() && ($parents = $category->getParentCategories())) {
            $pathIds = array_reverse($category->getPathIds());
            // Remove root category
            array_pop($pathIds);
            foreach($pathIds as $parentId) {
                if ($parentId == $rootId) {
                    break; // we are in root
                }
                if(isset($parents[$parentId]) && $parents[$parentId]
                        instanceof Mage_Catalog_Model_Category) {
                    $parentCategory = $parents[$parentId];
                    array_unshift($path, array(
						"name" => "category" . $parentCategory->getId(),
						"label" => $parentCategory->getName(),
						"link" => (($category->getId() == $parentId) && !$this->_getProduct())? 0:$parentCategory->getUrl()
					));
                }
            }
        }
        return $path;
    }

    /**
     * breadcrumb for product
     * @return array
     */
    protected function _getDefaultCategory() {
        $category = null;
        $rootId = $this->_getRootCategoryId();
        // if no category, try to get category from product
        if ($product = $this->_getProduct()) {
            /* @var $product Mage_Catalog_Model_Product */
            $catIds = $product->getCategoryIds();
            $collection = Mage::getResourceModel('catalog/category_collection');
            /* @var $collection Mage_Catalog_Model_Resource_Category_Collection */

            $collection->addAttributeToFilter("entity_id", array("in"=>$catIds));
            $collection->addAttributeToFilter("is_active", 1);
            $collection->addPathFilter("/$rootId/");
            // Get first category
            if($collection->count()) {
                $category = $collection->getFirstItem();
            }
        }
        return $category;
    }
    /**
     * breadcrumb for listing
     * @return array
     */
    protected function _prepareListingBreadcrumb() {
        $vendor = $this->_getVendor();
        $breadcrumbsBlock = $this->_getBlock();
        if($vendor) {
            $breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Mall'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::helper("zolagodropshipmicrosite")->getBaseUrl()
			));
            $type = $this->_getVendorTypeName($vendor->getVendorType());
            $breadcrumbsBlock->addCrumb('vendor', array(
				'label'=>$type . ' ' . Mage::helper('catalog')->__($vendor->getVendorName()),
				'title'=>Mage::helper('catalog')->__('Vendor'),
				'link'=>Mage::getBaseUrl()
			));
        } else {
            $breadcrumbsBlock->addCrumb('home', array(
				'label'=>Mage::helper('catalog')->__('Home'),
				'title'=>Mage::helper('catalog')->__('Go to Home Page'),
				'link'=>Mage::getBaseUrl()
			));
        }
    }

    /**
     * prepare breadcrumb block
     * @return
     */
    protected function _getBlock() {
        if (is_null($this->_breadcrumbBlock)) {
            if (!($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs'))) {
                $breadcrumbsBlock = $this->getLayout()->createBlock('page/html_breadcrumbs', 'breadcrumbs');
            }
            $this->_breadcrumbBlock = is_null($breadcrumbsBlock)? 0:$breadcrumbsBlock;
        }
        return $this->_breadcrumbBlock;
    }

    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Breadcrumbs
     */
    protected function _prepareLayout()
    {
        if(Mage::registry("bc_prepared")) {
            return $this;
        }
        $this->_prepareListingBreadcrumb();

        $title = array();
        $breadcrumbsBlock = $this->_getBlock();
        $path = $this->_getPath();
        foreach ($path as $name => $breadcrumb) {
            $breadcrumbsBlock->addCrumb($name, $breadcrumb);
            $title[] = $breadcrumb['label'];
        }

        if ($headBlock = $this->getLayout()->getBlock('head')) {
            $headBlock->setTitle(join($this->getTitleSeparator(), array_reverse($title)));
        }

        // Do not prapare bc again
        Mage::register("bc_prepared", true);
        return $this;
    }
}
