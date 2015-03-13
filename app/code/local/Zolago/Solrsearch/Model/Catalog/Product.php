<?php
/**
 * Use only for listing products
 */
class Zolago_Solrsearch_Model_Catalog_Product extends Zolago_Catalog_Model_Product {

	/**
	 * @return null | array
	 */
	public function getListingResizedImageInfo() {
        /** @var $_helper Zolago_Catalog_Helper_Product */
        $_helper = Mage::helper("zolagocatalog/product");
        return $_helper->getResizedImageInfo($this);
	}
	
	/**
	 * @todo test it
	 * @param type $qty
	 * @return type
	 */
	public function getFinalPrice($qty=null) {
		if($this->getCalculatedFinalPrice()!==null){
			return $this->getCalculatedFinalPrice();
		}
		return parent::getFinalPrice();
	}
	
	/**
	 * @return string
	 */
	public function getCurrency() {
		if($this->getStoreId()){
			$storeId = $this->getStoreId();
		}else{
			$storeId = Mage::app()->getStore()->getId();
		}
		return Mage::app()->getStore($storeId)->getCurrentCurrency()->getCode();
	}
	
	/**
	 * @return string | null;
	 */
	public function getListingResizedImageUrl() {
        /** @var Zolago_Solrsearch_Helper_Data $_helper */
        $_helper = Mage::helper("zolagosolrsearch");
		return $_helper->getListingResizedImageUrl($this);

	}
	
	
	/**
	 * @return string | null
	 */
	public function getUdropshipVendorLogoUrl() {
		if($this->getData("udropship_vendor_logo")){
			return Mage::getBaseUrl('media') . $this->getData("udropship_vendor_logo");
		}
		return null;
	}
	
	/**
	 * @return bool
	 */
	public function isDiscounted() {
		return $this->getFinalPrice()<$this->getPrice() && $this->getFinalPrice()>0 && $this->getPrice()>0;
	}
}