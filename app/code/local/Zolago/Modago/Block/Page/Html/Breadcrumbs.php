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
	 * @param type $param
	 */
	public function getBrandshopUrl() {
		if($this->getBrandshop()){
			return $this->getBrandshop()->getVendorUrl();
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
			$vendor = Mage::helper("umicrosite")->getCurrentVendor();
			if($vendor && $vendor->getId() && $vendor->isBrandshop()){
				$brandshop = $vendor;
			}
			$this->setData("brandshop", $brandshop);
		}
		return $this->getData("brandshop");
	}
}