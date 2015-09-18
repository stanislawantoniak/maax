<?php

class Zolago_Catalog_Vendor_ProductController 
    	extends Zolago_Catalog_Controller_Vendor_Product_Abstract {
	
    const ERROR_LIST_LEN = 10;
	/**
	 * Index
	 */
	public function indexAction() {
		$this->_saveHiddenColumns();
		$this->_renderPage(null, 'udprod_product');
    }
	
	
	
	/**
	 * Save attributes mass actions
	 */
	public function massAction() {
		
		$request =	$this->getRequest();
		$method = $request->getParam("method");
		$productIds = $request->getParam("product_ids");
		$attributeSetId = $request->getParam("attribute_set_id");
        $saveAsRule = $request->getParam("save_as_rule");
		$attributeMode = $request->getParam("attribute_mode");
		$storeId = $this->_getStoreId();
		$global = false;

		if(is_string($productIds)){
			$productIds = explode(",", $productIds);
		}
		$restQuery = array();
		if(is_array($productIds) && count($productIds)){
			$ids = array_unique($productIds);
		}else{
			$collection = $this->_getCollection();
			$restQuery = $this->_getRestQuery();
			foreach($restQuery as $key=>$value){
				$collection->addAttributeToFilter($key, $value);
			}
			$global = true;
			$ids = $collection->getAllIds();
		}

		try{
			array_walk($ids, function($value){
				return (int)$value;
			});
			
			$response = array(
				"changed_ids"	=> $ids,
				"global"		=> $global
			);
			
			switch ($method){
				/**
				 *Handle mass save
				 */
				case "attribute":
					$this->_processAttributresSave(
						$ids, 
						$request->getParam("attribute"), 
						$storeId, 
						array("attribute_mode"=>$request->getParam("attribute_mode"))
					);
				break;
				/**
				 * Handle status change
				 */
				case "confirm":
					$this->_validateProductAttributes($ids, $attributeSetId, $storeId);
					$this->_processAttributresSave(
						$ids, 
						array("description_status" => $this->_getSession()->getVendor()->getData("review_status")),
						$storeId, 
						array("check_editable"=>false)
					);
				break;
				default:
				    Mage::throwException("Invaild mass method");
			
			}
		} catch (Exception $ex) {
			$this->getResponse()->setHttpResponseCode(500);
			$response = $ex->getMessage();
			//$response = "Something went wrong. Contact admin.";
		}



		$this->getResponse()->setBody(Mage::helper("core")->jsonEncode($response));
		$this->_prepareRestResponse();


		$attributeCode = key($request->getParam("attribute"));
		$attributeValue = $request->getParam("attribute")[$attributeCode];

		Mage::dispatchEvent(
			"change_product_attribute_after",
			array(
				'store_id' => $storeId,
				"attribute_code" => $attributeCode,
				"vendor_id" => $this->getVendorId(),
				"attribute_mode" => $attributeMode[$attributeCode],
				"attribute_value" => $attributeValue,
				"rest_query" =>$restQuery,
				"save_as_rule" => $saveAsRule
			)
		);
	}
	
	
	
	/**
	 * Save hidden columns
	 */
	protected function _saveHiddenColumns() {
		if ($this->getRequest()->isPost()) {
			$listColumns = $this->getRequest()->getParam('listColumn',array());
  			$attributeSet = $this->getRequest()->getParam('attribute_set_id');
  			$hiddenColumns = $this->getRequest()->getParam('hideColumn',array());
			
			if(!Mage::getModel("eav/entity_attribute_set")->load($attributeSet)->getId()){
				return;
			}
			
  			foreach ($hiddenColumns as $key=>$dummy) {
  				unset($listColumns[$key]);
  			}
  			$session = Mage::getSingleton('udropship/session');
  			
  			$list = $session->getData('denyColumnList');
  			if (!$list) {
  				$list = array();
  			}
  			$list[$attributeSet] = $listColumns;
			
			$session->setData('denyColumnList',$list);
		}
	}	
	
    /**
     * Validate batch of products before theirs status will be set
     *
     * @throws Mage_Core_Exception
     * @param  array $productIds
     * @param  int $status
     * @return void
     */
    public function _validateMassStatus(array $productIds, $status)
    {
        if ($status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            if (!Mage::getModel('catalog/product')->isProductsHasSku($productIds)) {
                throw new Mage_Core_Exception(
                    $this->__('Some of the processed products have no SKU value defined. Please fill it prior to performing operations on these products.')
                );
            }
        }
    }
	
	/**
	 * @param type $productIds
	 * @param type $attributeSetId
	 * @param type $storeId
	 * @throws Mage_Core_Exception
	 */
	protected function _validateProductAttributes($productIds, $attributeSetId, $storeId) {
		$errorProducts	= array();
		$collection		= Mage::getResourceModel('zolagocatalog/product_collection');
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		$collection->setFlag("skip_price_data", true);
		$collection->setStoreId($storeId);
		$collection->addIdFilter($productIds);
		$collection->addAttributeToSelect('name');
		$collection->addAttributeToFilter("attribute_set_id", $attributeSetId);
		$collection->addAttributeToFilter("udropship_vendor", $this->_getSession()->getVendor()->getId());
		$collection->addAttributeToSelect('image');
		
		foreach ($collection as $product) {
		    if ($imageValidation = $this->_validateBaseImage($product)) {
		        $errorProducts[] = sprintf('%s: %s',$product->getName(),$imageValidation);
		    }
			if ($attributeValidation = $this->_validateRequiredAttributes($product, $storeId)) {
			    $errorProducts[] = Mage::helper('zolagocatalog')->__('%s: Empty required attributes (%s)',$product->getName(),implode(',',$attributeValidation));
			}
			if ($emptyValidation = $this->_validateEmptyImage($product)) {
			    $errorProducts[] = sprintf('%s: %s',$product->getName(),implode(',',$emptyValidation));
			}
		}
				
        $countErrorProducts = count($errorProducts);
        
		if ($countErrorProducts) {
		    if ($countErrorProducts > self::ERROR_LIST_LEN) {
		        $errorProducts = array_slice($errorProducts,0,self::ERROR_LIST_LEN);
		        $errorProducts[] = '...';
		    }    
			throw new Mage_Core_Exception(
				'<div class="alert alert-danger">'.Mage::helper('zolagocatalog')->__('Discovered %d validation problems:', $countErrorProducts) . "</div>" .
				implode("<br/>", $errorProducts)
			);	
		}
	}
	
	/**
	 * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
	 * @return boolean
	 */
	protected function _isVisibleInGrid(Mage_Catalog_Model_Resource_Eav_Attribute $attribute) {
		return in_array(
			$attribute->getGridPermission(), 
			$this->getGridModel()->getGridAttributeTypes()
		);
	}
	
	/**
	 * @param type $product
	 * @param type $storeId
	 * @return int
	 */
	protected function _validateRequiredAttributes($product, $storeId) 
	{
		/* @var $product Mage_Catalog_Model_Product */
		$missingAttributes = array();
		$attributes = $product->getAttributes();
		foreach ($attributes as $attribute) {
			if(in_array($attribute->getFrontendInput(), array("gallery", "weee"))){
				continue;
			}
			if ($attribute->getIsRequired() && $this->_isVisibleInGrid($attribute)) {
				/* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
				$value = Mage::getResourceModel('catalog/product')->getAttributeRawValue(
						$product->getId(), $attribute->getAttributeCode(), $storeId);                
				if ($attribute->isValueEmpty($value) || 
					(in_array($attribute->getFrontendInput(), array("select", "multiselect")) && $value===false)) {
					//Mage::log($attribute->getAttributeCode() . ": " . var_export($value, 1));
					$missingAttributes[$attribute->getAttributeCode()] = $attribute->getStoreLabel(
							$this->_getSession()->getVendor()->getLabelStore());
				}
			}
		}
		return $missingAttributes;
	}
	
	/**
	 * @param type $product
	 * @return text
	 */
	protected function _validateBaseImage($product) 
	{
		$validateImage = true;
		$baseImage = $product->getImage();
		if (empty($baseImage) || $baseImage == 'no_selection') {
		    return Mage::helper('zolagocatalog')->__('No base image');
		}		
		return null;
	}	
	
    /**     
     * @param type $product
     * @return array
     */
     protected function _validateEmptyImage($product) {
         $errors = array();
         $product->load('media_gallery');
         $gallery = $product->getMediaGalleryImages();
         foreach ($gallery as $image) {
             $path = $image->getPath();
             if (!@getimagesize($path)) {
                 $errors[] = Mage::helper('zolagocatalog')->__('Wrong image file format %s',$image->getFile());
             }
         }
         return $errors;
     }

}
