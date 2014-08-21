<?php
class Zolago_Catalog_Vendor_PriceController extends Zolago_Catalog_Controller_Vendor_Price_Abstract
{
	
	protected $_collection;
	
	/**
	 * Grid action
	 */
	public function indexAction() {
		$this->_renderPage(null, 'udprod_price');
	}
	
	
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
	 * Handle additional get action
	 */
	public function getAction() {
		$productId = $this->getRequest()->getParam("entity_id");
		$this->_handleRestGet($productId);
	}
	
	/**
	 * Handle rest put action
	 */
	protected function _handleRestPut() {
		
		$reposnse = $this->getResponse();
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
		$data['changed'] = array();

		$reposnse->setBody(json_encode($data));
		$this->_prepareRestResponse();
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
	 * @return Zolago_Catalog_Model_Resource_Vendor_Price_Collection
	 */
	protected function _getCollection() {
		if(!$this->_collection){
			// Add extra fields
			$collection = $this->_prepareCollection();
			$collection->addAttributes();
			$collection->joinAdditionalData();
			$this->_collection = $collection;
			
		}
		return $this->_collection;
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



