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
 * Product Carousel admin block
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Block_Adminhtml_Productcarousel extends Mage_Adminhtml_Block_Widget_Grid_Container{
	/**
	 * constructor
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function __construct(){
		$this->_controller 		= 'adminhtml_productcarousel';
		$this->_blockGroup 		= 'productcarousel';
		$this->_headerText 		= Mage::helper('productcarousel')->__('Product Carousel');
		$this->_addButtonLabel 	= Mage::helper('productcarousel')->__('Add Product Carousel');
		parent::__construct();
	}
}