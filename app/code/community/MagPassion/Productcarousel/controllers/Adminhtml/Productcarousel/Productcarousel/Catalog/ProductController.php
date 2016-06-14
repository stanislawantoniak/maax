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
 * Productcarousel product admin controller
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
require_once ("Mage/Adminhtml/controllers/Catalog/ProductController.php");
class MagPassion_Productcarousel_Adminhtml_Productcarousel_Productcarousel_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController{
	/**
	 * construct
	 * @access protected
	 * @return void
	 * @author MagPassion.com
	 */
	protected function _construct(){
		// Define module dependent translate
		$this->setUsedModuleName('MagPassion_Productcarousel');
	}
	/**
	 * productcarousels in the catalog page
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function productcarouselsAction(){
		$this->_initProduct();
		$this->loadLayout();
		$this->getLayout()->getBlock('product.edit.tab.productcarousel')
			->setProductProductcarousels($this->getRequest()->getPost('product_productcarousels', null));
		$this->renderLayout();
	}
	/**
	 * productcarousels grid in the catalog page
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function productcarouselsGridAction(){
		$this->_initProduct();
		$this->loadLayout();
		$this->getLayout()->getBlock('product.edit.tab.productcarousel')
			->setProductProductcarousels($this->getRequest()->getPost('product_productcarousels', null));
		$this->renderLayout();
	}
}