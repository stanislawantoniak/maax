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
		
		$out = array();
		
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
	 * Handle whole JOSN API
	 */
	public function restAction() {
		
		$profiler = Mage::helper("zolagocommon/profiler");
		/* @var $profiler Zolago_Common_Helper_Profiler */
		
		$profiler->start();
		
		$reposnse = $this->getResponse();
		
		switch ($this->getRequest()->getMethod()) {
			case "GET":
				$collection = $this->_getCollection();
				
				$select = $collection->getSelect();
				
				// Make filters
				foreach($this->_getRestQuery() as $key=>$value){
					$collection->addAttributeToFilter($key, $value);
				}
				
				// Make order and limit
				$out = $collection->prepareRestResponse(
						$this->_getRestSort(), 
						$this->_getRestRange()
				);
				
				$reposnse->
					setHeader('Content-Range', 'items ' . $out['start']. '-' . $out['end']. '/' . $out['total'])->
					setBody(Mage::helper("core")->jsonEncode($out['items']));
			break;
			case "PUT":
				$data = Mage::helper("core")->jsonDecode(($this->getRequest()->getRawBody()));
				$data['name'] = $data['name'] . " Edited";
				$reposnse->setBody(json_encode($data));
			break;
		}
		
		$reposnse->setHeader('Content-type', 'application/json');
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



