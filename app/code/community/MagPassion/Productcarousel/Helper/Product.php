<?php
/**
 * MagPassion_Productcarousel extension
 * 
 * @category   	MagPassion
 * @package		MagPassion_Productcarousel
 * @copyright  	Copyright (c) 2014 by MagPassion (http://magpassion.com)
 * @license	http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Product helper
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Helper_Product extends MagPassion_Productcarousel_Helper_Data{
	/**
	 * get the selected productcarousels for a product
	 * @access public
	 * @param Mage_Catalog_Model_Product $product
	 * @return array()
	 * @author MagPassion.com
	 */
	public function getSelectedProductcarousels(Mage_Catalog_Model_Product $product){
		if (!$product->hasSelectedProductcarousels()) {
			$productcarousels = array();
			foreach ($this->getSelectedProductcarouselsCollection($product) as $productcarousel) {
				$productcarousels[] = $productcarousel;
			}
			$product->setSelectedProductcarousels($productcarousels);
		}
		return $product->getData('selected_productcarousels');
	}
	/**
	 * get productcarousel collection for a product
	 * @access public
	 * @param Mage_Catalog_Model_Product $product
	 * @return MagPassion_Productcarousel_Model_Resource_Productcarousel_Collection
	 */
	public function getSelectedProductcarouselsCollection(Mage_Catalog_Model_Product $product){
		$collection = Mage::getResourceSingleton('productcarousel/productcarousel_collection')
			->addProductFilter($product);
		return $collection;
	}
}