<?php
class Zolago_DropshipMicrosite_Model_Observer
{
	protected $_websiteId			= false;
	protected $_vendor				= false;
	static protected $_vendorRootCategoryId = false;
	protected $_vendorRootCategory = false;
	
	
	
	
	
    public function __construct() {
        $this->_vendor = Mage::helper('umicrosite')->getCurrentVendor();
		$this->_websiteId			= Mage::app()->getWebsite()->getId();
		if (self::$_vendorRootCategoryId === false) { // only first time
		    $this->_initVendorRootCategory();
		}
    }	
	
	/**
	 * Replace microsite observer on product page.
	 * Include brandshop
	 * @param Mage_Core_Model_Observer $observer
	 * @return type
	 */
	public function catalogControllerProductInit($observer) {
		if (!($vendor = $this->_getVendor())
            || Mage::helper('umicrosite')->isCurrentVendorFromProduct()
        ) {
            return;
        }
        $product = $observer->getEvent()->getProduct();
        $isMyProduct = in_array(
				$vendor->getId(), array($product->getUdropshipVendor(), 
				$product->getBrandshop())
		);
        $showAll = Mage::getStoreConfigFlag('zolagoos/microsite/front_show_all_products');
        $isUdmulti = Mage::helper('udropship')->isUdmultiActive();
        $isInUdm = $product->getUdmultiStock($vendor->getId());
        if (!$isMyProduct && !($showAll && $isUdmulti && $isInUdm)) {
            Mage::throwException('Product is filtered out by vendor');
        }
	}
	
	protected function _getVendor()
    {
        return Mage::helper('umicrosite')->getCurrentVendor();
    }
	
	
    protected function _initVendorRootCategory() {
			if ($this->_vendor && $this->_vendor->getId()) {
				self::$_vendorRootCategoryId = (Mage::helper('zolagodropshipmicrosite')->getVendorRootCategoryConfigId($this->_vendor, $this->_websiteId));			
			}
    }	
	
	
	public function validateVendorCategory($observer)
	{
	    if (self::$_vendorRootCategoryId) {
	        // $vendorCategorId = Mage::helper('zolagodropshipmicrosite')->checkVendorCategoryId(self::$_vendorRootCategoryId);
    		$this->_vendorRootCategory	= Mage::getModel('catalog/category')->load(self::$_vendorRootCategoryId);
        }
		if ($this->_vendor && $this->_vendor->getId() && $this->_vendorRootCategory && $this->_vendorRootCategory->getId()) {
			$category = $observer->getEvent()->getCategory();
			if (strpos($category->getPath(), $this->_vendorRootCategory->getPath()) !== 0) {
				$redirectUrl = Mage::helper('zolagodropshipmicrosite')->getVendorRootUrl();
				Mage::app()->getFrontController()->getResponse()->setRedirect($redirectUrl);
			}
		}
	}
	
	public function changeVendorDesign($observer) {
		if ($this->_vendor && $this->_vendor->getId()) {
			// Update to new design
			Mage::helper('udropship')->loadCustomData($this->_vendor);
			$customDesign = $this->_vendor->getCustomDesign();
			$storeId = Mage::app()->getStore()->getId();
			if($customDesign && is_array($customDesign) && isset($customDesign[$storeId])){
				$customTheme = explode("/",$customDesign[$storeId]);
				if(count($customTheme)==2 && isset($customTheme[0]) && isset($customTheme[1])){
					Mage::getDesign()->setPackageName($customTheme[0]);
					Mage::getDesign()->setTheme($customTheme[1]);
				}
				
			}
		}
	}

    /**
     *
     * @param type $observer
     * @return \Zolago_DropshipMicrosite_Model_Observer
     */
    public function bindLocale($observer)
    {
        if(!Mage::registry("dropship_switch_lang")){
            return;
        }


        // Handle locale
        $session = Mage::getSingleton('udropship/session');

        if ($locale=$observer->getEvent()->getLocale()) {
            if ($choosedLocale = $session->getLocale()) {
                $locale->setLocaleCode($choosedLocale);
            }
        }
        return $this;
    }
}