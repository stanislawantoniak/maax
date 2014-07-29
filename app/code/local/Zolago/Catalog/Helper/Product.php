<?php
class Zolago_Catalog_Helper_Product extends Mage_Catalog_Helper_Product {
	
	const SIZE_PRESENTATION_IMAGES = "images";
	const SIZE_PRESENTATION_LIST = "list";
	
	const FLAG_EMPTY		= null;			// brak
	const FLAG_PROMOTION	= 'promotion';	// promotion (percent)
	const FLAG_SALE			= 'sale';		// wyprzedaz
	const FLAG_BESTSELLER	= 'bestseller'; // bsesseller (hit)
	const FLAG_NEW			= 'new';		// nowosc
	
	public function getSizePresentationType(Mage_Catalog_Model_Product $product) {
		if(!$product->getName()){
			return null;
		}
		return strlen($product->getName())%2 ? 
			self::SIZE_PRESENTATION_IMAGES : self::SIZE_PRESENTATION_LIST;
	}
	
	/**
	 * @param Mage_Catalog_Model_Product $product
	 * @return string
	 */
	public function getProductBestFlag(Mage_Catalog_Model_Product $product) {
		switch($product->getProductFlag()){
			case Zolago_Catalog_Model_Product_Source_Flag::FLAG_PROMOTION:
				return self::FLAG_PROMOTION;
			break;
			case Zolago_Catalog_Model_Product_Source_Flag::FLAG_SALE:
				return self::FLAG_SALE;
			break;
		}
		if((int)$product->getIsBestseller()){
			return self::FLAG_BESTSELLER;
		}
		if((int)$product->getIsNew()){
			return self::FLAG_NEW;
		}
		return self::FLAG_EMPTY;
	}
	
}