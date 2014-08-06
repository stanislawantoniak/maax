<?php
class Zolago_Catalog_Vendor_PriceController extends Zolago_Dropship_Controller_Vendor_Abstract
{
	
	protected $_collection;
	
	/**
	 * Grid action
	 */
	public function indexAction() {
		$this->_renderPage(null, 'udprod_price');
	}
	
	/**
	 * Load item details
	 */
	public function detailsAction() {
		$ids = $this->getRequest()->getParam("ids", array());
		$storeId = $this->getRequest()->getParam("store");
		$out = array();
		$collection = Mage::getResourceModel("zolagocatalog/vendor_price_detail_collection");
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Detail_Collection */
		$collection->setStoreId($storeId);
		
		
		
		foreach($ids as $id){
			$out[] = array(
				"entity_id" => $id,
				"var" => rand(0,10000)
			);
		}
		
		$this->getResponse()->
				setHeader('Content-type', 'application/json')->
				setBody(Mage::helper("core")->jsonEncode($out));
		
	}
	
	/**
	 * Get html of product price modal
	 */
	public function pricemodalAction() {
		
		$product = Mage::getModel('catalog/product')->load(
			$this->getRequest()->getParam('id')
		);
		
		if($product->getUdropshipVendor()!=$this->_getSession()->getVendorId()){
			$this->norouteAction();
			return;
		}
		
		Mage::register("current_product", $product);
		
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/**
	 * Handle whole JOSN API
	 */
	public function restAction() {
		
		$profiler = Mage::helper("zolagocommon/profiler");
		/* @var $profiler Zolago_Common_Helper_Profiler */
		
		$profiler->start();
		
		$reposnse = $this->getResponse();
		
		switch ($this->getRequest()->getMethod()) {
			case "GET":
				$productId = null;
				if(preg_match("/\/([0-9]+)$/", $this->getRequest()->getPathInfo(), $matches)){
					$productId = $matches[1];
				}
				
				$collection = $this->_getCollection();
				
				$select = $collection->getSelect();
				
				if($productId){
					$collection->addIdFilter($productId);
				}
				
				// Make filters
				foreach($this->_getRestQuery() as $key=>$value){
					$collection->addAttributeToFilter($key, $value);
				}
				
				// Make order and limit
				$out = $collection->prepareRestResponse(
						$this->_getRestSort(), 
						$this->_getRestRange()
				);
				
				if($productId && $out['items']){
					$reposnse->
						setBody(Mage::helper("core")->jsonEncode($out['items'][0]));
				}else{
					$reposnse->
						setHeader('Content-Range', 'items ' . $out['start']. '-' . $out['end']. '/' . $out['total'])->
						setBody(Mage::helper("core")->jsonEncode($out['items']));
				}
			break;
			case "PUT":
				$data = Mage::helper("core")->jsonDecode(($this->getRequest()->getRawBody()));
				
				try{
					$productIds = $data['entity_id'];
					$attributeChanged = $data['changed'];
					$attributeData = array();
					$storeId = $data['store_id'];
					
					foreach($attributeChanged as $attribute){
						if(isset($data[$attribute])){
							$attributeData[$attribute] = $data[$attribute];
						}
					}
					if($attributeData){
						$this->_processAttributresSave(array($productIds), $attributeData, $storeId);
					}
					
				} catch (Mage_Core_Exception $ex) {
					$reposnse->setHttpResponseCode(500);
					$reposnse->setBody($ex->getMessage());
					return;
				} catch (Exception $ex) {
					Mage::logException($ex);
					$reposnse->setHttpResponseCode(500);
					$reposnse->setBody("Some error occured");
					return;
				}
				
				/** dev tool **/
				$data['name'] = $data['name'] . " changed";
				$data['changed'] = array();
				
				$reposnse->setBody(json_encode($data));
			break;
		}
		
		$reposnse->setHeader('Content-type', 'application/json');
	}
	
	/**
	 * @param array $productIds
	 * @param array $attributes
	 * @param type $storeId
	 * @throws Mage_Core_Exception
	 */
	protected function _processAttributresSave(array $productIds, array $attributes, $storeId) {
		
		$collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
		$inventoryData = array();
		
		// Vaild collection
		$collection->addAttributeToFilter('udropship_vendor', $this->getVendor()->getId());
		$collection->addIdFilter($productIds);
		
		if($collection->getSize()<count($productIds)){
			throw new Mage_Core_Exception("You are trying to edit not your product");
		}
		
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Collection */
		
		foreach($attributes as $attributeCode=>$value){
			if(!in_array($attributeCode, $collection->getEditableAttributes())){
				throw new Mage_Core_Exception("You are trying to edit not editable attribute");
			}
			
			// Process modified flow attributes
			switch($attributeCode){
				case "display_price":
					$attributes['price'] = $value;
					unset($attributes[$attributeCode]);
				break;
				case "is_in_stock":
					$inventoryData['is_in_stock'] = $value;
					unset($attributes[$attributeCode]);
				break;
			
			}
		}
		
		
		$actionModel = Mage::getSingleton('catalog/product_action');
		/* @var $actionModel Mage_Catalog_Model_Product_Action */
		
		if($attributes){
			$actionModel->updateAttributes($productIds, $attributes, $storeId);
		}
		
		
		// Prepare stock
		foreach (Mage::helper('cataloginventory')->getConfigItemOptions() as $option) {
            if (isset($inventoryData[$option]) && !isset($inventoryData['use_config_' . $option])) {
                $inventoryData['use_config_' . $option] = 0;
            }
        }
		
		// Stock save
		if ($inventoryData) {
			/** @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
			$stockItem = Mage::getModel('cataloginventory/stock_item');
			$stockItem->setProcessIndexEvents(false);
			$stockItemSaved = false;

			foreach ($productIds as $productId) {
				$stockItem->setData(array());
				$stockItem->loadByProduct($productId)
					->setProductId($productId);

				$stockDataChanged = false;
				foreach ($inventoryData as $k => $v) {
					$stockItem->setDataUsingMethod($k, $v);
					if ($stockItem->dataHasChangedFor($k)) {
						$stockDataChanged = true;
					}
				}
				if ($stockDataChanged) {
					$stockItem->save();
					$stockItemSaved = true;
				}
			}

			if ($stockItemSaved) {
				Mage::getSingleton('index/indexer')->indexEvents(
					Mage_CatalogInventory_Model_Stock_Item::ENTITY,
					Mage_Index_Model_Event::TYPE_SAVE
				);
			}
		}
		
	}
	
	/**
	 * @return int
	 */
	protected function _getStoreId() {
		$storeId = $this->getRequest()->getParam("store_id");
		$store = Mage::app()->getStore($storeId);
		
		$allowedStores = $this->getAllowedStores();
		
		foreach($allowedStores as $_store){
			if($_store->getId()==$store->getId()){
				return $store->getId();
			}
		}
		
		throw new Mage_Core_Exception("Unknow store");
	}
	
	/**
	 * @return array
	 */
	public function getAllowedStores() {
		return Mage::helper("zolagodropship")->getAllowedStores($this->getVendor());
	}
	
	/**
	 * collection dont use after load - just flat selects
	 * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
	 */
	protected function _prepareCollection() {
		$visibilityModel = Mage::getSingleton("catalog/product_visibility");
		/* @var $visibilityModel Mage_Catalog_Model_Product_Visibility */
		$collection = Mage::getResourceModel("zolagocatalog/vendor_price_collection");
		/* @var $collection Zolago_Catalog_Model_Resource_Vendor_Price_Collection */
		
		$collection->setStoreId($this->_getStoreId());
		
		
		// Filter visible
		$collection->addAttributeToFilter("visibility", 
				array("neq"=>$visibilityModel::VISIBILITY_NOT_VISIBLE), "inner");
		
		// Filter dropship
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId(), "inner");
		
		// Add extra fields
		$collection->addAttributes();
		$collection->joinAdditionalData();
		
		return $collection;
	}
	
	/**
	 * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
	 */
	protected function _getCollection() {
		if(!$this->_collection){
			$this->_collection = $this->_prepareCollection();
		}
		return $this->_collection;
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return array
	 */
	protected function _getRestQuery() {
		$params = array();
		foreach($this->_getCollection()->getAvailableQueryParams() as $key){
			if(($value=$this->getRequest()->getQuery($key))!==null){
				if(is_string($value) && trim($value)==""){
					continue;
				}elseif(is_array($value) && !$value){
					continue;
				}
				$params[$key] = $this->_getSqlCondition($key, $value);
			}
		}
		return $params;
	}
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _getSqlCondition($key, $value) {
		if(is_array($value)){
			return $value;
		}
		switch ($key) {
			case "is_new":
			case "is_bestseller":
			case "product_flags":
			case "converter_price_type":
			case "is_in_stock":
				return array("eq"=>$value);
			break;
			case "msrp":
				return $value==1 ? array("notnull"=>true) : array("null"=>true, "neq"=>"");
			break;
		}
		return array("like"=>'%'.$value.'%');
	}
	
	
	
	/**
	 * @return array
	 */
	protected function _getRestRange() {
		$range = $this->getRequest()->getHeader("Range", 
			$this->getRequest()->getHeader("X-Range")
		);
		if($range){
			preg_match('/(\d+)-(\d+)/', $range, $matches);
			$start = $matches[1];
			$end = $matches[2];
		}else{
			$start = 0;
			$end = 100;
		}
		return array("start"=>$start, "end"=>$end);
	}
	
	/**
	 * @return array
	 */
	protected function _getRestSort() {
		$query = $this->getRequest()->getServer('QUERY_STRING');
		//sort(-entity_id)
		if(preg_match("/sort\((\-|\+)(\w+)\)/", $query, $matches)){
			if(in_array($matches[2], $this->_getCollection()->getAvailableSortParams())){
				return array(
					"order"=>$matches[2], 
					"dir"=>$matches[1]=="+" ? 
						Varien_Db_Select::SQL_ASC : Varien_Db_Select::SQL_DESC
				);
			}
		}
		return array();
	}
	
	

}



