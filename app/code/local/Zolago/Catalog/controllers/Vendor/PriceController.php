<?php
class Zolago_Catalog_Vendor_PriceController extends Zolago_Dropship_Controller_Vendor_Abstract
{
	
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
				$collection = $this->_prepareCollection();
				
				foreach($this->_getRestQuery() as $key=>$value){
					$collection->addAttributeToFilter($key, $value);
				}
				
				$out = $this->_prepareRestResponse($collection);

				$reposnse->
					setHeader('Content-Range', 'items ' . $out['start']. '-' . $out['end']. '/' . $out['total'])->
					setBody(Mage::helper("core")->jsonEncode($out['items']));
			break;
		}
		
		$reposnse->setHeader('Content-type', 'application/json');
	}
	
	/**
	 * collection dont use after load - just flat selects
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	protected function _prepareCollection() {
		$visibilityModel = Mage::getSingleton("catalog/product_visibility");
		/* @var $visibilityModel Mage_Catalog_Model_Product_Visibility */
		$collection = Mage::getResourceModel("catalog/product_collection");
		/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
		
		// Filter visible
		$collection->addAttributeToFilter("visibility", 
				array("neq"=>$visibilityModel::VISIBILITY_NOT_VISIBLE), "inner");
		// Filter dropship
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId(), "inner");
		
		// Add some attribs
		$collection->addAttributeToSelect("name", "left");

		return $collection;
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
		foreach($this->_getAvailableQueryParams() as $key){
			if(($value=$this->getRequest()->getQuery($key))!==null && trim($value)!=""){
				$params[$key] = array("like"=>'%'.$value.'%');
			}
		}
		return $params;
	}
	
	
	/**
	 * @param Mage_Catalog_Model_Resource_Product_Collection $collection
	 * @return array
	 */
	protected function _prepareRestResponse(Mage_Catalog_Model_Resource_Product_Collection $collection) {
		
		$select = $collection->getSelect();
		/* @var $select Varien_Db_Select */
		
		if($sort = $this->_getRestSort()){
			$select->order($sort['order'] . " " . $sort['dir']);
		}
		
		// Pepare total
		$totalSelect = clone $select;
		$adapter = $select->getAdapter();
		
		$totalSelect->reset(Zend_Db_Select::COLUMNS);
		$totalSelect->reset(Zend_Db_Select::ORDER);
		$totalSelect->resetJoinLeft();
		$totalSelect->columns(new Zend_Db_Expr("COUNT(e.entity_id)"));

		$total = $adapter->fetchOne($totalSelect);

		// Pepare range
		$range = $this->_getRestRange();
		$start = $range['start'];
		$end = $range['end'];
		if($end > $total){
			$end = $total;
		}
		// Make limit
		$select->limit($end-$start, $start);
		$items = $adapter->fetchAll($select);
		
		foreach($items as &$item){
			$item['can_collapse'] = true;//
		}
		
		return array(
			"items" => $items,
			"start" => $start,
			"end"	=> $end,
			"total" => $total
		); 
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
	 * @return array|null
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
		return null;
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		return array("name");
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		return array_merge($this->_getAvailableQueryParams(), array("entity_id", "sku", "type"));
	}

}



