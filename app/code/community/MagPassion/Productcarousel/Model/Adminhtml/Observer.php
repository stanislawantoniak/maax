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
 * Adminhtml observer
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Model_Adminhtml_Observer{
	/**
	 * check if tab can be added
	 * @access protected
	 * @param Mage_Catalog_Model_Product $product
	 * @return bool
	 * @author MagPassion.com
	 */
	protected function _canAddTab($product){
		if ($product->getId()){
			return true;
		}
		if (!$product->getAttributeSetId()){
			return false;
		}
		$request = Mage::app()->getRequest();
		if ($request->getParam('type') == 'configurable'){
			if ($request->getParam('attribtues')){
				return true;
			}
		}
		return false;
	}
	/**
	 * add the productcarousel tab to products
	 * @access public
	 * @param Varien_Event_Observer $observer
	 * @return MagPassion_Productcarousel_Model_Adminhtml_Observer
	 * @author MagPassion.com
	 */
	public function addProductcarouselBlock($observer){
		$block = $observer->getEvent()->getBlock();
		$product = Mage::registry('product');
		if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs && $this->_canAddTab($product)){
			$block->addTab('productcarousels', array(
				'label' => Mage::helper('productcarousel')->__('Productcarousels'),
				'url'   => Mage::helper('adminhtml')->getUrl('adminhtml/productcarousel_productcarousel_catalog_product/productcarousels', array('_current' => true)),
				'class' => 'ajax',
			));
		}
		return $this;
	}
	/**
	 * save productcarousel - product relation
	 * @access public
	 * @param Varien_Event_Observer $observer
	 * @return MagPassion_Productcarousel_Model_Adminhtml_Observer
	 * @author MagPassion.com
	 */
	public function saveProductcarouselData($observer){
		$post = Mage::app()->getRequest()->getPost('productcarousels', -1);
		if ($post != '-1') {
			$post = Mage::helper('adminhtml/js')->decodeGridSerializedInput($post);
			$product = Mage::registry('product');
			$productcarouselProduct = Mage::getResourceSingleton('productcarousel/productcarousel_product')->saveProductRelation($product, $post);
		}
		return $this;
	}}