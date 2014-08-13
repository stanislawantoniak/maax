<?php
class Zolago_Catalog_Vendor_Price_DetailController extends Zolago_Catalog_Controller_Vendor_Price_Abstract
{
	
	
	
	/**
	 * Get html of product price modal
	 */
	public function pricemodalAction() {
		
		$product = Mage::getModel('catalog/product')->
				setStoreId($this->getRequest()->getParam('store_id'))->
				load($this->getRequest()->getParam('id'));
		
		if($product->getUdropshipVendor()!=$this->_getSession()->getVendorId()){
			$this->norouteAction();
			return;
		}
		
		Mage::register("current_product", $product);
		
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Details action
	 */
	
	public function detailAction() {
		$ids = $this->getRequest()->getParam("ids", array());
		$storeId = $this->getRequest()->getParam("store");
		$out = array();
		
		$collection = $this->_prepareCollection();
		$collection->addIdFilter($ids);
		
		if($collection->getSize()<count($ids)){
			throw new Mage_Core_Exception("You are trying to edit not your product");
		}
		
		$out = Mage::getResourceSingleton('zolagocatalog/vendor_price')
				->getDetails($ids, $storeId, true, $this->_getSession()->isAllowed("campaign"));
		
		
		
		$this->getResponse()->
				setHeader('Content-type', 'application/json')->
				setBody(Mage::helper("core")->jsonEncode($out));
		
	}
	
	

}



