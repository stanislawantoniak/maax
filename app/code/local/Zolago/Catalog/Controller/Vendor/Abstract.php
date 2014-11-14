<?php
/**
 * @method Zolago_Dropship_Model_Session _getSession()
 */
abstract class Zolago_Catalog_Controller_Vendor_Abstract 
	extends Zolago_Dropship_Controller_Vendor_Abstract
{
	
	
	protected $_collection;
	
	/**
	 * Handle whole JOSN API
	 */
	public function restAction() {
		switch ($this->getRequest()->getMethod()) {
			case "GET":
				$productId = null;
				if(preg_match("/\/([0-9]+)$/", $this->getRequest()->getPathInfo(), $matches)){
					$productId = $matches[1];
				}
				$this->_handleRestGet($productId);
			break;
			case "PUT":
				$this->_handleRestPut();
			break;
		}
		
	}
	
	/**
	 * Prepare colection
	 * @param Varien_Data_Collection $collection
	 * @return Varien_Data_Collection
	 */
	protected  function _prepareCollection(Varien_Data_Collection $collection=null){
		return $collection;
	}
	

	
	/**
	 * @param array $inParams
	 * @return array
	 */
	protected function _getRestQuery(array $inParams = array()) {
		if(empty($inParams)){
			$inParams = $this->getRequest()->getQuery();
		}
		$params = array();
		foreach($this->_getAvailableQueryParams() as $key){
			if(isset($inParams[$key])){
				$value = $inParams[$key];
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
	 * handle Get method
	 */
	protected function _handleRestGet($productId=null) {
		$reposnse = $this->getResponse();
		
		$collection = $this->_getCollection();

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
		$this->_prepareRestResponse();
	}
	
	/**
	 * Prepare headers
	 */
	protected function _prepareRestResponse() {
		$this->getResponse()->setHeader('Content-type', 'application/json');
	}
	
	
	/**
	 * @param string $key
	 * @param mixed $value
	 * @return array
	 */
	protected function _getSqlCondition($key, $value) {
		if(is_array($value)){
			
			if(isset($value['to']) && is_numeric($value['to'])){
				$value['to'] = (float)$value['to'];
			}
			if(isset($value['from']) && is_numeric($value['from'])){
				$value['from'] = (float)$value['from'];
			}
			
			if(isset($value['to']) && is_numeric($value['to']) && 
					(!isset($value['from']) || (isset($value['from']) && $value['from']==0))){
				$value = array($value, array("null"=>true));
			}
			
			return $value;
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
	 * 
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		return array();
	}
	
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		return array();
	}
	
	/**
	 * @return array
	 */
	protected function _getRestSort() {
		$query = $this->getRequest()->getServer('QUERY_STRING');
		//sort(-entity_id)
		if(preg_match("/sort\((\-|\+)(\w+)\)/", $query, $matches)){
			if(in_array($matches[2], $this->_getAvailableSortParams())){
				return array(
					"order"=>$matches[2], 
					"dir"=>$matches[1]=="+" ? 
						Varien_Db_Select::SQL_ASC : Varien_Db_Select::SQL_DESC
				);
			}
		}
		return array();
	}

	/**
	 * @param string $number
	 * @return float
	 */
	protected function _formatNumber($number) {
		return (float) str_replace(",", ".", $number);
	}
	
	/**
	 * @param int $productId
	 * @param int $storeId
	 * @return Mage_Catalog_Model_Product
	 * @throws Mage_Core_Exception
	 */
	protected function _getProduct($productId, $storeId) {
		$product = Mage::getModel("catalog/product")->setStoreId($storeId)->load($productId);
		/* @var $product Mage_Catalog_Model_Product */
		if($product->getUdropshipVendor()==$this->_getSession()->getVendorId()){
			return $product;
		}
		throw new Mage_Core_Exception("Product not allowed");
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
	 * @return Mage_Core_Model_Store
	 */
	public function getStore() {
		return Mage::app()->getStore($this->_getStoreId());
	}
	
	/**
	 * @return array
	 */
	public function getAllowedStores() {
		return Mage::helper("zolagodropship")->getAllowedStores($this->getVendor());
	}

	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return int
	 */
	public function getVendorId() {
		return $this->getVendor()->getId();
	}
	

}



