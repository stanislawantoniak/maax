<?php
class Zolago_DropshipMicrosite_Model_Observer
{
	protected $_websiteId			= false;
	protected $_vendor				= false;
	protected $_vendorRootCategory	= false;
	
    public function __construct() {
        if (!$this->_vendorRootCategory && !$this->_websiteId) {
			$this->_vendor = Mage::helper('umicrosite')->getCurrentVendor();
			if ($this->_vendor && $this->_vendor->getId()) {
				$this->_websiteId			= Mage::app()->getWebsite()->getId();
				$rootCategoryId				= (Mage::helper('zolagodropshipmicrosite')->getVendorRootCategory($this->_vendor, $this->_websiteId));			
				$this->_vendorRootCategory	= Mage::getModel('catalog/category')->load($rootCategoryId);
			}
        }
    }
	
	public function validateVendorCategory($observer)
	{
		if ($this->_vendor && $this->_vendor->getId() && $this->_vendorRootCategory && $this->_vendorRootCategory->getId()) {
			$category = $observer->getEvent()->getCategory();
			if (strpos($category->getPath(), $this->_vendorRootCategory->getPath()) !== 0) {
				$redirectUrl = Mage::helper('zolagodropshipmicrosite')->getVendorRootUrl();
				Mage::app()->getFrontController()->getResponse()->setRedirect($redirectUrl);
			}
		}
	}
}