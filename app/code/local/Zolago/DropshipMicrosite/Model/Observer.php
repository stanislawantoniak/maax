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