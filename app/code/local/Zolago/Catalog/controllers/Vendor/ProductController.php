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
	public function saveMassAction() {
		
		$request = $this->getRequest();
		$productIds = $request->getParam("product_ids");
		$data = $request->getParam("attribute");
		$storeId = $this->_getStoreId();
		$global = false;
		
		if(is_array($productIds) && count($productIds)){
			$collection = $this->_prepareBasciCollection();
			$collection->addIdFilter($productIds);
		}else{
			$collection = $this->_getCollection();
			foreach($this->_getRestQuery() as $key=>$value){
				$collection->addAttributeToFilter($key, $value);
			}
			$global = true;
		}
		
		try{
			$ids = $collection->getAllIds();
			
			array_walk($ids, function($value){
				return (int)$value;
			});
			
			$this->_processAttributresSave(
					$ids, 
					$data, 
					$storeId, 
					$request->getPost()
			);
			
			$response = array(
				"changed_ids"	=> $ids,
				"data"			=> $data,
				"global"		=> $global
			);
		} catch (Exception $ex) {
			$this->getResponse()->setHttpResponseCode(500);
			$response = $ex->getMessage();
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
     * Update product(s) status action
     *
     */
    public function massStatusAction()
    {
        $productIds			= array_unique(explode(',', $this->getRequest()->getParam('product_ids', '')));
        $storeId			= (int)$this->getRequest()->getParam('store', 0);
		$attributeSet		= (int)$this->getRequest()->getParam('attribute_set', null);
        $status				= (int)$this->getRequest()->getParam('status');
		$staticFiltersCount	= (int)$this->getRequest()->getParam('staticFilters');
		$productReview		= (int)$this->getRequest()->getParam('review', null);
		
		$staticFilters		= $this->_getCurrentStaticFilterValues();
		$postParams			= array('store'=> $storeId, 'attribute_set' => $attributeSet, 'staticFilters' => $staticFiltersCount);
		$postParams			= array_merge($postParams, $staticFilters);

        $response = array();

        try {

			if ($productReview) {
				$this->_validateProductAttributes($productIds, $attributeSet, $storeId);
                $response = array(
                    "status"=>1,
                    "content"=>$this->__('Total of %d record(s) have been validated.', count($productIds))
                );
			}

            $status = Mage::helper('zolagodropship')->getProductStatusForVendor($this->_getSession()->getVendor());
            $this->_validateMassStatus($productIds, $status);
            Mage::getSingleton('catalog/product_action')
                ->updateAttributes($productIds, array('status' => $status), $storeId);

            $response = array(
                "status"=>1,
                "content"=>$this->__('Total of %d record(s) have been updated.', count($productIds))
            );
        }
        catch (Mage_Core_Model_Exception $e) {
            $response = array(
                "status"=>0,
                "content"=>Mage::helper("zolagocatalog")->__($e->getMessage())
            );
        } catch (Mage_Core_Exception $e) {
            $response = array(
                "status"=>0,
                "content"=>Mage::helper("zolagocatalog")->__($e->getMessage())
            );
        } catch (Exception $e) {
            $response = array(
                "status"=>0,
                "content"=>Mage::helper("zolagocatalog")->__($this->__('An error occurred while updating the product(s) status.'))
            );
        }

        // Send response
        $this->getResponse()->
            setBody(Zend_Json::encode($response))->
            setHeader('content-type', 'application/json');
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
