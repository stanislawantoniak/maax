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
 * Product Carousel - product relation model
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Model_Resource_Productcarousel_Product extends Mage_Core_Model_Resource_Db_Abstract{
/**
	 * initialize resource model
	 * @access protected
	 * @return void
	 * @see Mage_Core_Model_Resource_Abstract::_construct()
	 * @author MagPassion.com
	 */
	protected function  _construct(){
		$this->_init('productcarousel/productcarousel_product', 'rel_id');
	}
	/**
	 * Save productcarousel - product relations
	 * @access public
	 * @param MagPassion_Productcarousel_Model_Productcarousel $productcarousel
	 * @param array $data
	 * @return MagPassion_Productcarousel_Model_Resource_Productcarousel_Product
	 * @author MagPassion.com
	 */
	public function saveProductcarouselRelation($productcarousel, $data){
		if (!is_array($data)) {
			$data = array();
		}
		$deleteCondition = $this->_getWriteAdapter()->quoteInto('productcarousel_id=?', $productcarousel->getId());
		$this->_getWriteAdapter()->delete($this->getMainTable(), $deleteCondition);

		foreach ($data as $productId => $info) {
			$this->_getWriteAdapter()->insert($this->getMainTable(), array(
				'productcarousel_id'  	=> $productcarousel->getId(),
				'product_id' 	=> $productId,
				'position'  	=> @$info['position']
			));
		}
		return $this;
	}
	/**
	 * Save  product - productcarousel relations
	 * @access public
	 * @param Mage_Catalog_Model_Product $prooduct
	 * @param array $data
	 * @return MagPassion_Productcarousel_Model_Resource_Productcarousel_Product
	 * @@author MagPassion.com
	 */
	public function saveProductRelation($product, $data){
		if (!is_array($data)) {
			$data = array();
		}
		$deleteCondition = $this->_getWriteAdapter()->quoteInto('product_id=?', $product->getId());
		$this->_getWriteAdapter()->delete($this->getMainTable(), $deleteCondition);
		
		foreach ($data as $productcarouselId => $info) {
			$this->_getWriteAdapter()->insert($this->getMainTable(), array(
				'productcarousel_id' => $productcarouselId,
				'product_id' => $product->getId(),
				'position'   => @$info['position']
			));
		}
		return $this;
	}
}