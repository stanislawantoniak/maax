<?php
class Zolago_Catalog_Vendor_Price_DetailController extends Zolago_Catalog_Controller_Vendor_Price_Abstract
{
	/**
	 * Stockmodal
	 */
	public function stockmodalAction() {

		$this->_registerProduct();
		
		$this->loadLayout();
		$this->renderLayout();
	}
	/**
	 * Save prices
	 * @return type
	 */
	public function pricemodalSaveAction() {
		
		$request = $this->getRequest();
		$productId = $request->getParam("entity_id");
		$storeId = $request->getParam("store_id");
		$attributes = $request->getParam("attributes");
		$msrp = $this->_formatNumber($request->getParam("msrp", 0));
		$margin = $this->_formatNumber($request->getParam("price_margin", 0));
		
		$productData = array(
			"converter_price_type" => $request->getParam("converter_price_type"),
			"price_margin"	=> $margin != 0 ? $margin : null,
			"price"			=> $this->_formatNumber($request->getParam("price")),
			"msrp"			=> $msrp > 0 ? $msrp : null
		);
		
		try{
			$data = array();
			
			
			// Save price variations
			if($attributes){
				foreach($attributes as $superAttrId=>$values){
					foreach($values as $valueId=>$pricing){
						$variation = $this->_formatNumber($pricing['pricing_value']);
						$data[$pricing['product_super_attribute_id']][] = array(
							"product_super_attribute_id" => 
								$pricing['product_super_attribute_id'],
							"value_index"		=> $pricing['value_index'],
							"pricing_value"		=> $variation != 0 ? $variation : "",
							"value_id"			=> $pricing['value_id'],
							"is_percent"		=> 0, // only fixed
							"use_default_value" => 0, // use website values
						);
					}
				}

				$model = Mage::getModel('catalog/product_type_configurable_attribute');
				/* @var $model Mage_Catalog_Model_Product_Type_Configurable_Attribute */

				foreach($data as $superAttrId => $values){
					$model->setId($superAttrId);
					$model->setStoreId($storeId);
					$model->setProductId($productId);
					$model->setValues($values);
					$model->getResource()->savePrices($model);
				}
			}
			
			// Save product prices
			
			$this->_processAttributresSave(array($productId), $productData, $storeId);
			
		}
		catch(Exception $e){
			Mage::logException($e);
			$this->getResponse()->
					setHttpResponseCode(500)->
					setBody($e->getMessage())->
					setHeader("content-type", "text/plain");
			return;
		}
		
		$this->_forward("get", "vendor_price", "udprod");
	}
	
	/**
	 * Get html of product price modal (HTML)
	 */
	public function pricemodalAction() {
		
		$this->_registerProduct();
		
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Details action (JSON)
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
	
	protected function _registerProduct() {
		$product = Mage::getModel('catalog/product')->
				setStoreId($this->getRequest()->getParam('store_id'))->
				load($this->getRequest()->getParam('id'));
		
		if($product->getUdropshipVendor()!=$this->_getSession()->getVendorId()){
			$this->norouteAction();
			return;
		}
		
		Mage::register("current_product", $product);
	}

}



