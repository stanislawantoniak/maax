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
 * Product Carousel model
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Model_Productcarousel extends Mage_Core_Model_Abstract{
	/**
	 * Entity code.
	 * Can be used as part of method name for entity processing
	 */
	const ENTITY= 'productcarousel_productcarousel';
	const CACHE_TAG = 'productcarousel_productcarousel';
	/**
	 * Prefix of model events names
	 * @var string
	 */
	protected $_eventPrefix = 'productcarousel_productcarousel';
	
	/**
	 * Parameter name in event
	 * @var string
	 */
	protected $_eventObject = 'productcarousel';
	protected $_productInstance = null;
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function _construct(){
		parent::_construct();
		$this->_init('productcarousel/productcarousel');
	}
	/**
	 * before save product carousel
	 * @access protected
	 * @return MagPassion_Productcarousel_Model_Productcarousel
	 * @author MagPassion.com
	 */
	protected function _beforeSave(){
		parent::_beforeSave();
		$now = Mage::getSingleton('core/date')->gmtDate();
		if ($this->isObjectNew()){
			$this->setCreatedAt($now);
		}
		$this->setUpdatedAt($now);
		return $this;
	}
	/**
	 * get the url to the product carousel details page
	 * @access public
	 * @return string
	 * @author MagPassion.com
	 */
	public function getProductcarouselUrl(){
		return Mage::getUrl('productcarousel/productcarousel/view', array('id'=>$this->getId()));
	}
	/**
	 * save productcarousel relation
	 * @access public
	 * @return MagPassion_Productcarousel_Model_Productcarousel
	 * @author MagPassion.com
	 */
	protected function _afterSave() {
		$this->getProductInstance()->saveProductcarouselRelation($this);
		return parent::_afterSave();
	}
	/**
	 * get product relation model
	 * @access public
	 * @return MagPassion_Productcarousel_Model_Productcarousel_Product
	 * @author MagPassion.com
	 */
	public function getProductInstance(){
		if (!$this->_productInstance) {
			$this->_productInstance = Mage::getSingleton('productcarousel/productcarousel_product');
		}
		return $this->_productInstance;
	}
	/**
	 * get selected products array
	 * @access public
	 * @return array
	 * @author MagPassion.com
	 */
	public function getSelectedProducts(){
		if (!$this->hasSelectedProducts()) {
			$products = array();
			foreach ($this->getSelectedProductsCollection() as $product) {
				$products[] = $product;
			}
			$this->setSelectedProducts($products);
		}
		return $this->getData('selected_products');
	}
	/**
	 * Retrieve collection selected products
	 * @access public
	 * @return MagPassion_Productcarousel_Resource_Productcarousel_Product_Collection
	 * @author MagPassion.com
	 */
	public function getSelectedProductsCollection(){
		$collection = $this->getProductInstance()->getProductCollection($this);
		return $collection;
	}
}