<?php
/**
 * Use only for listing products
 */
class Zolago_Solrsearch_Model_Catalog_Product extends Mage_Catalog_Model_Product {

	/**
	 * @return null | array
	 */
	public function getListingResizedImageInfo() {
		$urlPath = $this->getListingResizedImageUrl();
		// Extract cached image URI
		if($urlPath){
			$filePath = substr($urlPath, strpos($urlPath, "//")+2);
			$filePath = substr($filePath, strpos($filePath, "/")+1);
			$filePath = str_replace("/", DS, $filePath);
			if($info=@getimagesize($filePath)){
				return array("width"=>$info[0], "height"=>$info[1]);
			}
		}
		return null;
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
		if(!$this->hasData("listing_resized_image_url")){
			/** 
			* @todo move configurable values of helpert do admin conif per website
			*/
		   $return = null;
		   try{
			   $return = Mage::helper('catalog/image')->
				   init($this, 'image')->
				   keepAspectRatio(true)->
				   constrainOnly(true)->
				   keepFrame(false)->
				   resize(300,null);
		   } catch (Exception $ex) {
			   Mage::logException($ex);
		   }
		   $this->setData("listing_resized_image_url", $return . ""); // Cast to string
		}
		
		return $this->getData("listing_resized_image_url");
		
	}
	
	/**
	 * @return string|null
	 */
	public function getManufacturerLogoUrl() {
		if($this->getData("manufacturer_logo")){
			return Mage::getBaseUrl('media') . $this->getData("manufacturer_logo");
		}
		return null;
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