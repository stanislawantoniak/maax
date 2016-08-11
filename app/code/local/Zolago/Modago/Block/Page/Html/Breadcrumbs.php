<?php
/**
 * Zolago Modago Breadcrumbs
 *
 * @category   Zolago
 * @package    Zolago_Modago
 * @author     <victoria.sultanovska@convertica.pl>
 */
class Zolago_Modago_Block_Page_Html_Breadcrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    public function addCrumb($crumbName, $crumbInfo, $after = false)
    {
        $this->_prepareArray($crumbInfo, array('label', 'title', 'link', 'first', 'last', 'readonly', 'id'));
        if ((!isset($this->_crumbs[$crumbName])) || (!$this->_crumbs[$crumbName]['readonly'])) {
            $this->_crumbs[$crumbName] = $crumbInfo;
        }
        return $this;
    }
	
	/**
	 * @return bool
	 */
	public function getCanShowBrandshop() {
		return $this->isCatalogProductViewAction() && $this->getBrandshop();
	}

    /**
     * @return bool
     */
    public function getCanShowStandardVendor() {
        return $this->isCatalogProductViewAction() && $this->getStandardVendor();
    }

    /**
     * @return string URL
     */
	public function getBrandshopUrl() {
		if($this->getBrandshop()){
			return $this->getBrandshop()->getVendorUrl();
		}
		return $this->getUrl("/");
	}

    /**
     * @return string URL
     */
    public function getStandardVendorUrl() {
        if($this->getStandardVendor()){
            return $this->getStandardVendor()->getVendorUrl();
        }
        return $this->getUrl("/");
    }
	
	/**
	 * @return boolean
	 */
	public function isCatalogProductViewAction() {
		$request = $this->getRequest();
		if($request->getActionName()=="view" && 
	       $request->getControllerName()=="product" && 
		   $request->getModuleName()=="catalog"){
			return true;
		}
		return false;
	}
	
	/**
	 * @return Zolago_Dropship_Model_Vendor | false
	 */
	public function getBrandshop() {
		if(!$this->hasData("brandshop")){
			$brandshop = false;
            /** @var Zolago_Dropship_Model_Vendor $vendor */
			$vendor = Mage::helper("umicrosite")->getCurrentVendor();
			if($vendor && $vendor->getId() && $vendor->isBrandshop()){
				$brandshop = $vendor;
			}
			$this->setData("brandshop", $brandshop);
		}
		return $this->getData("brandshop");
	}

    /**
     * @return Zolago_Dropship_Model_Vendor | false
     */
    public function getStandardVendor() {
        if(!$this->hasData("standard_vendor")){
            $standardVendor = false;
            /** @var Zolago_Dropship_Model_Vendor $vendor */
            $vendor = Mage::helper("umicrosite")->getCurrentVendor();
            if($vendor && $vendor->getId() && $vendor->isStandard()){
                $standardVendor = $vendor;
            }
            $this->setData("standard_vendor", $standardVendor);
        }
        return $this->getData("standard_vendor");
    }

    /**
     * @return boolean
     */
    public function isSearchContext(){
        $request = $this->getRequest();
        return (
            $request->getModuleName()=="search" &&
            $request->getControllerName()=="index" &&
            $request->getActionName()=="index"
        );
    }

}