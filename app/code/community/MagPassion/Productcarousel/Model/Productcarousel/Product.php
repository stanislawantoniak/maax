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
 * Product Carousel product model
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Model_Productcarousel_Product extends Mage_Core_Model_Abstract{
	/**
	 * Initialize resource
	 * @access protected
	 * @return void
	 * @author MagPassion.com
	 */
	protected function _construct(){
		$this->_init('productcarousel/productcarousel_product');
	}
	/**
	 * Save data for productcarousel-product relation
	 * @access public
	 * @param  MagPassion_Productcarousel_Model_Productcarousel $productcarousel
	 * @return MagPassion_Productcarousel_Model_Productcarousel_Product
	 * @author MagPassion.com
	 */
	public function saveProductcarouselRelation($productcarousel){
		$data = $productcarousel->getProductsData();
		if (!is_null($data)) {
			$this->_getResource()->saveProductcarouselRelation($productcarousel, $data);
		}
		return $this;
	}
	/**
	 * get products for productcarousel
	 * @access public
	 * @param MagPassion_Productcarousel_Model_Productcarousel $productcarousel
	 * @return MagPassion_Productcarousel_Model_Resource_Productcarousel_Product_Collection
	 * @author MagPassion.com
	 */
	public function getProductCollection($productcarousel){
		$collection = Mage::getResourceModel('productcarousel/productcarousel_product_collection')
			->addProductcarouselFilter($productcarousel);
		return $collection;
	}
}