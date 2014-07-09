<?php
class Zolago_DropshipMicrosite_Helper_Data extends Mage_Core_Helper_Abstract
{
	const URL_UNSECURE_PREFIX	= 'http://';
	const URL_SECURE_PREFIX		= 'https://';
	const URL_MODE_PATH			= 'udropship/microsite/subdomain_level';
	
	const URL_MODE_DEFAULT		= 1; //URL Pattern: http://www.baseurl.com/vendor_url
	const URL_MODE_SUBDOMAIN	= 3; //URL Pattern: http://vendor_url.baseurl.com
	const URL_REDIRECT_MODE		= 301;
	
	public function getVendorRootUrl()
	{
		$_vendor		= Mage::helper('umicrosite')->getCurrentVendor();
		$vendorUrlKey	= $_vendor->getUrlKey();
		$urlMode		= Mage::getStoreConfig(self::URL_MODE_PATH);
		$unsecureUrl	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$secureUrl		= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
		
		switch ($urlMode) {
			case self::URL_MODE_SUBDOMAIN:
				$vendorRootUrl = $this->getSubdomainVendorUrl($unsecureUrl, $vendorUrlKey);
				if (Mage::app()->getStore()->isCurrentlySecure()) {
					$vendorRootUrl = $this->getSubdomainVendorUrl($secureUrl, $vendorUrlKey);
				}
				break;
			default:
				$vendorRootUrl = $unsecureUrl . $vendorUrlKey . DS;
				if (Mage::app()->getStore()->isCurrentlySecure()) {
					$vendorRootUrl = $secureUrl . $vendorUrlKey . DS;
				}
				break;
		}

		if (!$vendorUrlKey) {
			$vendorRootUrl = $unsecureUrl;
		}

		return $vendorRootUrl;		
	}

	public function getVendorCurrentUrl()
	{
		$currentUrl = $this->getVendorRootUrl();
		if (!in_array(Mage::app()->getFrontController()->getAction()->getFullActionName(), array('cms_index_noRoute', 'cms_index_defaultNoRoute', 'umicrosite_index_landingPage'))) {
			$currentUrl = rtrim(Mage::helper('core/url')->getCurrentUrl(), DS) . DS;
		}
		return $currentUrl;
	}


	
    /**
     * @param Unirgy_Dropship_Model_Vendor $vendor
     * @param int                          $websiteId
     * @return int $rootCategoryConfigId     
     */
    public function getVendorRootCategoryConfigId($vendor,$websiteId) {
		$rootCategoryId = 0;
		Mage::helper('udropship')->loadCustomData($vendor);
		$rootCategories = $vendor->getRootCategory();
		if (array_key_exists($websiteId, $rootCategories)) {
			$rootCategoryConfigId = $rootCategories[$websiteId];
		}
		return $rootCategoryConfigId;    	
    }
    
    /**
     * check if root category exists
     * 
     * @param int $rootCategoryConfigId
     * @return int
     */
    public function checkVendorRootCategory($rootCategoryConfigId) {
    	$rootCategoryId = 0;
		$rootCategory = Mage::getModel('catalog/category')->load($rootCategoryConfigId);
		if ($rootCategory && $rootCategory->getId()) {
			$rootCategoryId = $rootCategoryConfigId;
		}
		return $rootCategoryId;
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
		$rootCategoryId = 0;
		$rootCategoryConfigId = $this->getVendorRootCategoryConfigId($vendor,$websiteId);
		if ($rootCategoryConfigId) {
			$rootCategoryId = $this->checkVendorRootCategory($rootCategoryConfigId);
		}
		return $rootCategoryId;
	}
	
	public function getSubdomainVendorUrl($rootUrl, $vendorKey)
	{
		$urlParts	= explode('//', $rootUrl);
		$vendorUrl	= array_shift($urlParts);
		$vendorUrl .= '//' . $vendorKey . '.';
		
		foreach ($urlParts as $urlPart) {
			$vendorUrl .= $urlPart;
		}
		
		return $vendorUrl;
	}
	
	public function getBaseUrl($store=null) {
		$baseUnsecure = $base = Mage::app()->getStore($store)->getConfig("web/unsecure/base_url");
		if(Mage::app()->getRequest()->isSecure()){
			$baseSecure = Mage::app()->getStore($store)->getConfig("web/secure/base_url");
			$base = str_replace("{{base_url}}", $baseUnsecure, $baseSecure);
		}
		return $base;
	}
}