<?php
/**
 * Use only for listing products
 */
class Zolago_Solrsearch_Model_Catalog_Product extends Mage_Catalog_Model_Product {
	
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
	 * @return string
	 */
	public function getListingResizedImageUrl() {
		/** 
		 * @todo move configurable values of helpert do admin conif per website
		 */
		return Mage::helper('catalog/image')->
				init($this, 'image')->
				keepAspectRatio(true)->
				constrainOnly(true)->
				keepFrame(false)->
				resize(214,null);
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