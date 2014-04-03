<?php

class Zolago_Catalog_Vendor_MassController 
	extends Zolago_Dropship_Controller_Vendor_Abstract {
	/**
	 * Index
	 */
	public function indexAction() {
		Mage::register('as_frontend', true);// Tell block class to use regular URL's
		
		$this->_renderPage(array('default', 'formkey', 'adminhtml_head'), 'zolagocatalog');
	}
	
	public function saveAjaxAction() {
		$response = array();
		if($this->getRequest()->isPost()){
			
			// Attributes data array('code'=>'value',...)
			$attributes = $this->getRequest()->getPost("attributes");
			// Attribure set
			$attributeSet = $this->_getAttributeSet();
			// Store scope
			$store = $this->_getStore();
			
			if(!is_array($attributes) || !count($attributes) || !$attributeSet || !$attributeSet->getId() || !$store){
				$response = array(
					"status"=>0, 
					"content"=>Mage::helper("zolagocatalog")->__("No required data")
				);
			}else{
				if($this->_validateAttributes($attributes, $attributeSet, $notMatched)){
					// Zapis
					// Zwrotka w tej postaci
					$response = array(
						"status"=>1, 
						"content"=>array(
							"attributes_changed"	=>	array_keys($attributes),
							"matched_product"		=>	10
						)
					);
				}else{
					$response = array(
						"status"=>0, 
						"content"=>Mage::helper("zolagocatalog")->
							__("There is problem vith attribute premission (%s)", implode(",", $notMatched))
					);
				}
			}
		}else{
			$response = array(
				"status"=>0, 
				"content"=>Mage::helper("zolagocatalog")->__("Wrogn HTTP method")
			);
		}
		
		
		$this->getResponse()->
				setBody(Zend_Json::encode($response))->
				setHeader('content-type', 'application/json');
	}


	public function gridAction(){
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$this->loadLayout();
		$block = $this->getLayout()->createBlock("zolagocatalog/vendor_mass_grid");

		$this->getResponse()->setBody($block->toHtml());
	}
	
	public function massDeleteAction() {
		var_export($this->getRequest()->getParams());
	}
	
	/**
	 * @param type $attributes
	 * @return Mage_Catalog_Model_Resource_Product_Attribute_Collection
	 */
	protected function _validateAttributes($attributes, $attributeSet, &$notMatched) {
		
		$collection = Mage::getResourceModel("catalog/product_attribute_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */		

		$collection->setAttributeSetFilter($attributeSet->getId());

		$collection->addFieldToFilter("grid_permission", 
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION);
		
		$keys = array_keys($attributes);
		$collection->addFieldToFilter("attribute_code", array("in"=>$keys));
		
		$notMatched = array();
		
		foreach($keys as $attributeCode){
			if(!$collection->getItemByColumnValue("attribute_code", $attributeCode)){
				$notMatched[] = $attributeCode;
			}
		}
		
		return count($notMatched)==0;
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	protected function _getStore() {
		return Mage::app()->getStore(
			Mage::app()->getRequest()->getParam("store", 0)
		);
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	protected function _getAttributeSet() {
		return Mage::getModel("eav/entity_attribute_set")->load(
			Mage::app()->getRequest()->getParam("attribute_set")
		);
	}
	
}


