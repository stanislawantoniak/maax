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
 * Product Carousel admin controller
 *
 * @category	MagPassion
 * @package		MagPassion_Productcarousel
 * @author MagPassion.com
 */
class MagPassion_Productcarousel_Adminhtml_Productcarousel_ProductcarouselController extends MagPassion_Productcarousel_Controller_Adminhtml_Productcarousel{
	/**
	 * init the productcarousel
	 * @access protected
	 * @return MagPassion_Productcarousel_Model_Productcarousel
	 */
	protected function _initProductcarousel(){
		$productcarouselId  = (int) $this->getRequest()->getParam('id');
		$productcarousel	= Mage::getModel('productcarousel/productcarousel');
		if ($productcarouselId) {
			$productcarousel->load($productcarouselId);
		}
		Mage::register('current_productcarousel', $productcarousel);
		return $productcarousel;
	}
 	/**
	 * default action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function indexAction() {
		$this->loadLayout();
		$this->_title(Mage::helper('productcarousel')->__('Productcarousel'))
			 ->_title(Mage::helper('productcarousel')->__('Product Carousels'));
		$this->renderLayout();
	}
	/**
	 * grid action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function gridAction() {
		$this->loadLayout()->renderLayout();
	}
	/**
	 * edit product carousel - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function editAction() {
		$productcarouselId	= $this->getRequest()->getParam('id');
		$productcarousel  	= $this->_initProductcarousel();
		if ($productcarouselId && !$productcarousel->getId()) {
			$this->_getSession()->addError(Mage::helper('productcarousel')->__('This product carousel no longer exists.'));
			$this->_redirect('*/*/');
			return;
		}
		$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
		if (!empty($data)) {
			$productcarousel->setData($data);
		}
		Mage::register('productcarousel_data', $productcarousel);
		$this->loadLayout();
		$this->_title(Mage::helper('productcarousel')->__('Productcarousel'))
			 ->_title(Mage::helper('productcarousel')->__('Product Carousels'));
		if ($productcarousel->getId()){
			$this->_title($productcarousel->getBlocktitle());
		}
		else{
			$this->_title(Mage::helper('productcarousel')->__('Add product carousel'));
		}
		if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) { 
			$this->getLayout()->getBlock('head')->setCanLoadTinyMce(true); 
		}
		$this->renderLayout();
	}
	/**
	 * new product carousel action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function newAction() {
		$this->_forward('edit');
	}
	/**
	 * save product carousel - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function saveAction() {
		if ($data = $this->getRequest()->getPost('productcarousel')) {
			try {
				$productcarousel = $this->_initProductcarousel();
				$productcarousel->addData($data);
				$products = $this->getRequest()->getPost('products', -1);
				if ($products != -1) {
					$productcarousel->setProductsData(Mage::helper('adminhtml/js')->decodeGridSerializedInput($products));
				}
				$productcarousel->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productcarousel')->__('Product Carousel was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $productcarousel->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
			} 
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
			catch (Exception $e) {
				Mage::logException($e);
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was a problem saving the product carousel.'));
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Unable to find product carousel to save.'));
		$this->_redirect('*/*/');
	}
	/**
	 * delete product carousel - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function deleteAction() {
		if( $this->getRequest()->getParam('id') > 0) {
			try {
				$productcarousel = Mage::getModel('productcarousel/productcarousel');
				$productcarousel->setId($this->getRequest()->getParam('id'))->delete();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productcarousel')->__('Product Carousel was successfully deleted.'));
				$this->_redirect('*/*/');
				return; 
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error deleteing product carousel.'));
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
				Mage::logException($e);
				return;
			}
		}
		Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Could not find product carousel to delete.'));
		$this->_redirect('*/*/');
	}
	/**
	 * mass delete product carousel - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massDeleteAction() {
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels to delete.'));
		}
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
					$productcarousel = Mage::getModel('productcarousel/productcarousel');
					$productcarousel->setId($productcarouselId)->delete();
				}
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('productcarousel')->__('Total of %d product carousels were successfully deleted.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error deleteing product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass status change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massStatusAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setStatus($this->getRequest()->getParam('status'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Type change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massTypeAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setType($this->getRequest()->getParam('flag_type'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Navigation Skin change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massSkinAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setSkin($this->getRequest()->getParam('flag_skin'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show block title change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowblocktitleAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowblocktitle($this->getRequest()->getParam('flag_showblocktitle'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show product name change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowproductnameAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowproductname($this->getRequest()->getParam('flag_showproductname'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show product image change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowproductimageAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowproductimage($this->getRequest()->getParam('flag_showproductimage'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show product price change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowproductpriceAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowproductprice($this->getRequest()->getParam('flag_showproductprice'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show add to cart button change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowproductaddtocartAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowproductaddtocart($this->getRequest()->getParam('flag_showproductaddtocart'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show more content of product change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowproductmoreAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowproductmore($this->getRequest()->getParam('flag_showproductmore'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show more: product price change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowmorepriceAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowmoreprice($this->getRequest()->getParam('flag_showmoreprice'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show more: product review change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowmorereviewAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowmorereview($this->getRequest()->getParam('flag_showmorereview'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show more: product short description change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowmoredesAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowmoredes($this->getRequest()->getParam('flag_showmoredes'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show more: product add to cart change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowmoreaddtocartAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowmoreaddtocart($this->getRequest()->getParam('flag_showmoreaddtocart'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * mass Show more: prouduct add to link change - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function massShowmoreaddtolinkAction(){
		$productcarouselIds = $this->getRequest()->getParam('productcarousel');
		if(!is_array($productcarouselIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('Please select product carousels.'));
		} 
		else {
			try {
				foreach ($productcarouselIds as $productcarouselId) {
				$productcarousel = Mage::getSingleton('productcarousel/productcarousel')->load($productcarouselId)
							->setShowmoreaddtolink($this->getRequest()->getParam('flag_showmoreaddtolink'))
							->setIsMassupdate(true)
							->save();
				}
				$this->_getSession()->addSuccess($this->__('Total of %d product carousels were successfully updated.', count($productcarouselIds)));
			}
			catch (Mage_Core_Exception $e){
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('productcarousel')->__('There was an error updating product carousels.'));
				Mage::logException($e);
			}
		}
		$this->_redirect('*/*/index');
	}
	/**
	 * get grid of products action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function productsAction(){
		$this->_initProductcarousel();
		$this->loadLayout();
		$this->getLayout()->getBlock('productcarousel.edit.tab.product')
			->setProductcarouselProducts($this->getRequest()->getPost('productcarousel_products', null));
		$this->renderLayout();
	}
	/**
	 * get grid of products action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function productsgridAction(){
		$this->_initProductcarousel();
		$this->loadLayout();
		$this->getLayout()->getBlock('productcarousel.edit.tab.product')
			->setProductcarouselProducts($this->getRequest()->getPost('productcarousel_products', null));
		$this->renderLayout();
	}
	/**
	 * export as csv - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function exportCsvAction(){
		$fileName   = 'productcarousel.csv';
		$content	= $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_grid')->getCsv();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	/**
	 * export as MsExcel - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function exportExcelAction(){
		$fileName   = 'productcarousel.xls';
		$content	= $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_grid')->getExcelFile();
		$this->_prepareDownloadResponse($fileName, $content);
	}
	/**
	 * export as xml - action
	 * @access public
	 * @return void
	 * @author MagPassion.com
	 */
	public function exportXmlAction(){
		$fileName   = 'productcarousel.xml';
		$content	= $this->getLayout()->createBlock('productcarousel/adminhtml_productcarousel_grid')->getXml();
		$this->_prepareDownloadResponse($fileName, $content);
	}
}