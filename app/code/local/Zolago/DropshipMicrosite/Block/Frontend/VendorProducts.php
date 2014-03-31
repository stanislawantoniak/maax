<?php
class Zolago_DropshipMicrosite_Block_Frontend_VendorProducts extends Unirgy_DropshipMicrosite_Block_Frontend_VendorProducts
{
    protected function _construct()
    {
        parent::_construct();
		$this->setShowRootCategory(true);
	}
    protected function _getProductCollection()
    {
		$_vendor		= Mage::helper('umicrosite')->getCurrentVendor();
		$websiteId		= Mage::app()->getWebsite()->getId();
		$rootCategoryId = Mage::helper('zolagodropshipmicrosite')->getVendorRootCategory($_vendor, $websiteId);
		
		$layer = $this->getLayer();
		if (is_null($this->_productCollection)) {
            /* @var $layer Mage_Catalog_Model_Layer */
            if ($this->getShowRootCategory()) {
                $this->setCategoryId($rootCategoryId);
            }

            // if this is a product view page
            if (Mage::registry('product')) {
                // get collection of categories this product is associated with
                $categories = Mage::registry('product')->getCategoryCollection()
                    ->setPage(1, 1)
                    ->load();
                // if the product is associated with any category
                if ($categories->count()) {
                    // show products from this category
                    $this->setCategoryId(current($categories->getIterator()));
                }
            }

            $origCategory = null;
            if ($this->getCategoryId()) {
                $category = Mage::getModel('catalog/category')->load($this->getCategoryId());
                if ($category->getId()) {
                    $origCategory = $layer->getCurrentCategory();
                    $layer->setCurrentCategory($category);
                }
            } elseif (!$layer->udApplied) {
                $oldIsAnchor = $layer->getCurrentCategory()->getIsAnchor();
                $layer->getCurrentCategory()->setIsAnchor(true);
            }
            $collection = $layer->getProductCollection();
            if (isset($oldIsAnchor)) {
                $layer->getCurrentCategory()->setIsAnchor($oldIsAnchor);
            }

            $this->prepareSortableFieldsByCategory($layer->getCurrentCategory());

            if ($origCategory) {
                $layer->setCurrentCategory($category);
            }
			
            $this->_addProductAttributesAndPrices($collection);
            Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
            Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);

            Mage::helper('umicrosite')->addVendorFilterToProductCollection($collection);

            $this->_productCollection = $collection;
        }
		
		if (!$rootCategoryId) {
			$redirectUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			Mage::app()->getFrontController()->getResponse()->setRedirect($redirectUrl);
		} else {
			$vendorUrl = Mage::helper('zolagodropshipmicrosite')->getVendorCurrentUrl();
			$redirectUrl = $vendorUrl.$layer->getCurrentCategory()->getUrlPath();
			Mage::app()->getFrontController()->getResponse()->setRedirect($redirectUrl, Zolago_DropshipMicrosite_Helper_Data::URL_REDIRECT_MODE);	
		}

        return $this->_productCollection;
    }	
}