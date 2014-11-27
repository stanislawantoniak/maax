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
			
			switch ($method){
				case "attribute":
					$this->_processAttributresSave(
						$ids, 
						$request->getParam("attribute"), 
						$storeId, 
						array("attribute_mode"=>$request->getParam("attribute_mode"))
					);
				break;
				case "disable":
				case "confirm":
					$status = Mage::helper('zolagodropship')->getProductStatusForVendor(
						$this->_getSession()->getVendor()
					);
					$this->_validateMassStatus($ids, $status);
					Mage::throwException("Methid $method $status");
					$this->_processAttributresSave(
						$ids, 
						array("status"=>$status), 
						$storeId, 
						array("attribute_mode"=>$mode, "check_editable"=>false)
					);
				break;
				default:
					Mage::throwException("Invaild mass method");
			
			}
			$response = array(
				"changed_ids"	=> $ids,
				"global"		=> $global
			);
		} catch (Mage_Core_Exception $ex) {
			$this->getResponse()->setHttpResponseCode(500);
			$response = $ex->getMessage();
		} catch (Exception $ex) {
			$this->getResponse()->setHttpResponseCode(500);
			$response = "Something went wrong. Contact admin.";
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
}
