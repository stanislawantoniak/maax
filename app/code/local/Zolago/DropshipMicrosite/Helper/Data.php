<?php
class Zolago_DropshipMicrosite_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getVendorRootUrl()
	{
		$_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		$vendorRootUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . $_vendor->getUrlKey() . DS;
		if (Mage::app()->getStore()->isCurrentlySecure()) {
			$vendorRootUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true) . $_vendor->getUrlKey() . DS;
		}
		return $vendorRootUrl;		
	}

	public function getVendorCurrentUrl()
	{
		$currentUrl = $this->getVendorRootUrl();
		if (!in_array(Mage::app()->getFrontController()->getAction()->getFullActionName(), array('cms_index_noRoute', 'cms_index_defaultNoRoute'))) {
			$currentUrl = rtrim(Mage::helper('core/url')->getCurrentUrl(), DS) . DS;
		}
		return $currentUrl;
	}

	/**
	 * Get Root Category Id or False
	 * 
	 * @param Unirgy_Dropship_Model_Vendor	$vendor		Vendor Object
	 * @param int							$websiteId	Website Id
	 * 
	 * @return int $rootCategoryId Root Category Id of Vendor
	 */
	public function getVendorRootCategory($vendor, $websiteId)
	{
		$rootCategoryId = false;
		Mage::helper('udropship')->loadCustomData($vendor);
		$rootCategories = $vendor->getRootCategory();
		if (array_key_exists($websiteId, $rootCategories)) {
			$rootCategoryConfigId = $rootCategories[$websiteId];
			$rootCategory = Mage::getModel('catalog/category')->load($rootCategoryConfigId);
			if ($rootCategory && $rootCategory->getId()) {
				$rootCategoryId = $rootCategoryConfigId;
			}
		}
		return $rootCategoryId;
	}
}