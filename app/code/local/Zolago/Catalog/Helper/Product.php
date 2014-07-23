<?php
class Zolago_Catalog_Helper_Product extends Mage_Catalog_Helper_Product {
	
	const SIZE_PRESENTATION_IMAGES = "images";
	const SIZE_PRESENTATION_LIST = "list";
	
	public function getSizePresentationType(Mage_Catalog_Model_Product $product) {
		if(!$product->getName()){
			return null;
		}
		return strlen($product->getName())%2 ? 
			self::SIZE_PRESENTATION_IMAGES : self::SIZE_PRESENTATION_LIST;
	}
	
}