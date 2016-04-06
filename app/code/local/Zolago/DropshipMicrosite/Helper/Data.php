<?php
class Zolago_DropshipMicrosite_Helper_Data extends Mage_Core_Helper_Abstract
{
	const URL_UNSECURE_PREFIX	= 'http://';
	const URL_SECURE_PREFIX		= 'https://';
	const URL_MODE_PATH			= 'udropship/microsite/subdomain_level';
	
	const URL_MODE_DEFAULT		= 1; //URL Pattern: http://www.baseurl.com/vendor_url
	const URL_MODE_SUBDOMAIN	= 3; //URL Pattern: http://vendor_url.baseurl.com
	const URL_REDIRECT_MODE		= 301;
	
	protected $_rootCategory;
	
	/**
	 * @param string $url
	 * @return string
	 */
	public function convertToNonVendorContext($url, $vendor=null) {
		
		if($vendor===null){
			$vendor	= Mage::helper("umicrosite")->getCurrentVendor();
		}
		if(!$vendor instanceof Unirgy_Dropship_Model_Vendor){
			return $url;
		}
		
		$urlMode		= Mage::getStoreConfig(self::URL_MODE_PATH);
		$unsecureUrl	= strpos($url, "http://") === 0;
		$secureUrl		= strpos($url, "https://") === 0;
		$urlKey			= $vendor->getUrlKey();
		$urlTmp			= $url;
		
		if($unsecureUrl){
			$urlTmp = str_replace("http://", "", $urlTmp);
		}elseif($secureUrl){
			$urlTmp = str_replace("https://", "", $urlTmp);
		}
		
		switch ($urlMode) {
			case 2:
			case 3:
			case 4:
			case 5:
				// Remove first subdomain
				$urlKey = strtolower($urlKey);
				$urlTmp = preg_replace("/^".$urlKey."\./", "", $urlTmp);
			case 1:
				// Remove slash
				// Possible bug when address is domain.com/vendorKey/vendorKey
				Mage::log($urlTmp);
				Mage::log($urlKey);
				$urlTmp = preg_replace("/\/".$urlKey."\//", "/", $urlTmp);
				Mage::log($urlTmp);
				
		}

		if($unsecureUrl){
			$urlTmp = "http://"  .$urlTmp;
		}elseif($secureUrl){
			$urlTmp = "https://"  .$urlTmp;
		}
		
		return $urlTmp;
	}
	
	/**
	 * Return vendor root category if exists or store root category
     * if no vendor specified current vendor by default is taken
     * @param Zolago_Dropship_Model_Vendor $vendor
	 * @return Mage_Catalog_Model_Category
	 */
	public function getVendorRootCategoryObject($vendor = null) {
		if(!$this->_rootCategory){
            if(empty($vendor) || !($vendor instanceof Zolago_Dropship_Model_Vendor)) {
                $vendor = Mage::helper('umicrosite')->getCurrentVendor();
            }
			$categoryId = null;
			if($vendor && $vendor->getId()){
				Mage::helper('udropship')->loadCustomData($vendor);
				$rootCats = $vendor->getRootCategory();
				$websiteId = Mage::app()->getWebsite()->getId();
				if(isset($rootCats[$websiteId])){
					$categoryId = $rootCats[$websiteId]; 
				}
			}
			
			if(!$categoryId){
				$categoryId = Mage::app()->getStore()->getRootCategoryId();
			}
			
			$this->_rootCategory = Mage::getModel("catalog/category")->load($categoryId);
		}
		return $this->_rootCategory;
	}
	
	protected function _getVendorUrl($_vendor=null, $forceInsecure = FALSE) {
		if(!($_vendor instanceof Unirgy_Dropship_Model_Vendor)){
			$_vendor	= Mage::helper('umicrosite')->getCurrentVendor();
		}
		if(!($_vendor instanceof Unirgy_Dropship_Model_Vendor)){
			return Mage::getUrl("/");
		}
		
		$vendorUrlKey	= $_vendor->getUrlKey();
		$urlMode		= Mage::getStoreConfig(self::URL_MODE_PATH);
		$unsecureUrl	= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
		$secureUrl		= Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, true);
		if($forceInsecure){
			$secureUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB, false);
		}

		switch ($urlMode) {
			case self::URL_MODE_SUBDOMAIN:
			case 2:
			case 3:
			case 4:
				$vendorRootUrl = $this->getSubdomainVendorUrl($unsecureUrl, strtolower($vendorUrlKey));
				if (Mage::app()->getStore()->isCurrentlySecure()) {
					$vendorRootUrl = $this->getSubdomainVendorUrl($secureUrl, strtolower($vendorUrlKey));
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
	
	public function getVendorUrl($vendor=null, $forceInsecure = FALSE) {
		return $this->_getVendorUrl($vendor, $forceInsecure);
	}
	
	public function getVendorRootUrl()
	{
		return $this->_getVendorUrl();
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
        if (!is_array($rootCategories)) {
            return $rootCategoryId;
        }
        if (array_key_exists($websiteId,$rootCategories)) {
			$rootCategoryId = $rootCategories[$websiteId];
		}
		return $rootCategoryId;
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

	public function getCurrentVendor()
	{
		return Mage::helper('umicrosite/protected')->getCurrentVendor();
	}


}