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
			
			// Products Ids
			$productIds = array_unique(
					explode(",", $this->getRequest()->getPost("product_ids", ""))
			);
			// Attributes data array('code'=>'value',...)
			$attributesData = $this->getRequest()->getPost("attributes");
			// Attrbiute modes
			$attributesMode = $this->getRequest()->getPost("attributes_mode");
			// Attribure set
			$attributeSet = $this->_getAttributeSet();
			// Store scope
			$store = $this->_getStore();
			
			$helper = Mage::helper("zolagocatalog");
			
			$dateFormat = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
			
			try{
				if(!is_array($attributesData) || !count($attributesData) || 
					!$attributeSet || !$attributeSet->getId() || 
					!$store || !is_array($productIds) || !count($productIds)){
					
					throw new Mage_Core_Exception(
							$helper->__("No required data passed")
					);
				}
				if(!$this->_validateAttributes($attributesData, $attributeSet, $notMatched, $collection)){
					throw new Mage_Core_Exception(
							$helper->__("There is problem with attribute premission (%s)", implode(",", $notMatched))
					);
				}
				if(!$this->_validateProductIds($productIds, $attributeSet, $store)){
					throw new Mage_Core_Exception(
							$helper->__("You are trying save not Your product(s).")
					);
				}
				
				foreach ($attributesData as $attributeCode => $value) {
					
                    $attribute =$collection->getItemByColumnValue("attribute_code", $attributeCode);
					
					if(!$attribute || !$attribute->getId()){
						unset($attributesData[$attributeCode]);
						continue;
					}
					// Prepare date fileds
                    if ($attribute->getBackendType() == 'datetime') {
                        if (!empty($value)) {
                            $filterInput    = new Zend_Filter_LocalizedToNormalized(array(
                                'date_format' => $dateFormat
                            ));
                            $filterInternal = new Zend_Filter_NormalizedToLocalized(array(
                                'date_format' => Varien_Date::DATE_INTERNAL_FORMAT
                            ));
                            $value = $filterInternal->filter($filterInput->filter($value));
                        } else {
                            $value = null;
                        }
                        $attributesData[$attributeCode] = $value;
                    }elseif ($attribute->getFrontendInput() == 'multiselect') {
                        if (is_array($value)) {
                            $attributesData[$attributeCode] = implode(',', $value);
                        }
						
						// Unset value if add mode active
						if(isset($attributesMode[$attributeCode])){
							switch ($attributesMode[$attributeCode]) {
								case "add":
									Mage::getResourceSingleton('zolagocatalog/vendor_mass')->addValueToMultipleAttribute(
										$productIds,
										$attribute, 
										is_array($value) ? $value : array(),
										$store
									);
									unset($attributesData[$attributeCode]);
								break;
							}
						}
                    }
                }
				
				// Write attribs & make reindex
                Mage::getSingleton('catalog/product_action')
                    ->updateAttributes($productIds, $attributesData, $store->getId());
				
				$response = array(
					"status"=>1, 
					"content"=>array(
						"attributes"=>	array_keys($attributesData),
						"count"		=>	count($productIds)
					)
				);
			}catch(Mage_Core_Exception $e){
				$response = array(
					"status"=>0, 
					"content"=>$e->getMessage()
				);
			}catch(Exception $e){
				$response = array(
					"status"=>0, 
					"content"=>$helper->__("Some error occured. Contact administrator.")
				);
				Mage::logException($e);
			}
		}else{
			$response = array(
				"status"=>0, 
				"content"=>Mage::helper("zolagocatalog")->__("Wrogn HTTP method")
			);
		}
		// Send response
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
	protected function _validateAttributes($attributes, $attributeSet, &$notMatched, &$collection) {
		
		$collection = Mage::getResourceModel("catalog/product_attribute_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Attribute_Collection */		

		$collection->setAttributeSetFilter($attributeSet->getId());

		$collection->addFieldToFilter("grid_permission", 
				Zolago_Eav_Model_Entity_Attribute_Source_GridPermission::EDITION);
		
		$keys = array_keys($attributes);
		$collection->addFieldToFilter("attribute_code", array("in"=>$keys));
		$collection->addIsNotUniqueFilter();
		
		$notMatched = array();
		
		foreach($keys as $attributeCode){
			if(!$collection->getItemByColumnValue("attribute_code", $attributeCode)){
				$notMatched[] = $attributeCode;
			}
		}
		
		return count($notMatched)==0;
	}
	
	
	protected function _validateProductIds($productIds, $attributeSet, $store){
		$collection = Mage::getResourceModel("catalog/product_collection");
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->setFlag("skip_price_data", true);
		if($store->getId()){
			$collection->setStoreId($store->getId());
		}
		$collection->addIdFilter($productIds);
		$collection->addAttributeToFilter("attribute_set_id", $attributeSet->getId());
		$collection->addAttributeToFilter("udropship_vendor", $this->_getSession()->getVendor()->getId());
		$collection->addAttributeToFilter("visibility", array("in"=>array(
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG, 
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_SEARCH, 
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH 
		)));
		return count(array_diff($productIds, $collection->getAllIds()))==0;
	}
	
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	protected function _getStore() {
		$storeId = Mage::app()->getRequest()->getParam("store");
		$candidate = Mage::app()->getStore($storeId);
		if($candidate->getId()==$storeId){
			return $candidate;
		}
		return Mage::app()->getStore(Mage_Core_Model_Store::ADMIN_CODE);
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


