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
 * Product Carousel - product relation resource model collection
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Model_Resource_Productcarousel_Product_Collection extends Mage_Catalog_Model_Resource_Product_Collection{
	/**
	 * remember if fields have been joined
	 * @var bool
	 */
	protected $_joinedFields = false;
	/**
	 * join the link table
	 * @access public
	 * @return MagPassion_Productcarousel_Model_Resource_Productcarousel_Product_Collection
	 * @author MagPassion.com
	 */
	public function joinFields(){
		if (!$this->_joinedFields){
			$this->getSelect()->join(
				array('related' => $this->getTable('productcarousel/productcarousel_product')),
				'related.product_id = e.entity_id',
				array('position')
			);
			$this->_joinedFields = true;
		}
		return $this;
	}
	/**
	 * add productcarousel filter
	 * @access public
	 * @param MagPassion_Productcarousel_Model_Productcarousel | int $productcarousel
	 * @return MagPassion_Productcarousel_Model_Resource_Productcarousel_Product_Collection
	 * @author MagPassion.com
	 */
	public function addProductcarouselFilter($productcarousel){
		if ($productcarousel instanceof MagPassion_Productcarousel_Model_Productcarousel){
			$productcarousel = $productcarousel->getId();
		}
		if (!$this->_joinedFields){
			$this->joinFields();
		}
		$this->getSelect()->where('related.productcarousel_id = ?', $productcarousel);
		return $this;
	}
}