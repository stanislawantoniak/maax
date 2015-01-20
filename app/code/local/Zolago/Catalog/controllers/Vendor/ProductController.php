<?php

class Zolago_Catalog_Vendor_ProductController 
	extends Zolago_Catalog_Controller_Vendor_Product_Abstract {
	
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
		$storeId = $this->_getStoreId();
		$global = false;
		
		if(is_string($productIds)){
			$productIds = explode(",", $productIds);
		}
		
		if(is_array($productIds) && count($productIds)){
			$ids = array_unique($productIds);
		}else{
			$collection = $this->_getCollection();
			foreach($this->_getRestQuery() as $key=>$value){
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
				case "disable":
				case "confirm":
					if($method=="confirm"){
						$this->_validateProductAttributes($ids, $attributeSetId, $storeId);
						$status = Mage::helper('zolagodropship')->getProductStatusForVendor(
							$this->_getSession()->getVendor()
						);
					}else{
						$status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
					}
					
					$this->_validateMassStatus($ids, $status);
					$this->_processAttributresSave(
						$ids, 
						array("status"=>$status), 
						$storeId, 
						array("check_editable"=>false)
					);
				break;
				default:
					Mage::throwException("Invaild mass method");
			
			}
		} catch (Mage_Core_Exception $ex) {
			$this->getResponse()->setHttpResponseCode(500);
			$response = $ex->getMessage();
		} catch (Exception $ex) {
			$this->getResponse()->setHttpResponseCode(500);
			$response = $ex->getMessage();
			//$response = "Something went wrong. Contact admin.";
		}
		
		
		$this->getResponse()->setBody(Mage::helper("core")->jsonEncode($response));
		$this->_prepareRestResponse();
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
			$imageValidation		= $this->_validateBaseImage($product);
			$attributeValidation	= $this->_validateRequiredAttributes($product, $storeId);
			if (!$imageValidation || count($attributeValidation) > 0) {
				$errorProducts[$product->getId()]['name']		= $product->getName();
				$errorProducts[$product->getId()]['image']		= $imageValidation;
				$errorProducts[$product->getId()]['missing']	= implode(", ", $attributeValidation);
			}
		}
		
		$logArray = array();
		foreach($errorProducts as $error){
			if(count($logArray)>10){
				break;
			}
			$log = array();
			if(!$error['image']){
				$log[] = $this->__('No base image');
			}
			if($error['missing']){
				$log[] = $error['missing'];
			}
			$logArray[] = $error['name'] . ": " . implode(",", $log);
		}
		
		$errorProductCount = count($errorProducts);
		if ($errorProductCount>0) {
			throw new Mage_Core_Exception(
				$this->__('%d selected product has empty required attribute(s) and/or is missing a base image:', $errorProductCount) . "\n" .
				implode("\n", $logArray)
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
	 * @return boolean
	 */
	protected function _validateBaseImage($product) 
	{
		$validateImage = true;
		$baseImage = $product->getImage();
		if (empty($baseImage) || $baseImage == 'no_selection') {
			$validateImage = false;
		}
		
		return $validateImage;		
	}	
}
