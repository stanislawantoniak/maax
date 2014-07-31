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
			case "PUT":
				$data = Mage::helper("core")->jsonDecode(($this->getRequest()->getRawBody()));
				$data['name'] = $data['name'] . " Edited";
				$reposnse->setBody(json_encode($data));
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
		
		$storeId = 1;
		$store = Mage::app()->getStore($storeId);
		
		$collection->setStoreId($storeId);
		
		
		// Filter visible
		$collection->addAttributeToFilter("visibility", 
				array("neq"=>$visibilityModel::VISIBILITY_NOT_VISIBLE), "inner");
		// Filter dropship
		$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId(), "inner");
		
		// Add non-o attribs
		$attributesToSelect = array(
			"converter_price_type",
			"price_margin",
			//"campaign_regular_id",
			//"campaign_info_id",
			"msrp",
			"is_new",
			"is_bestseller",
			"status",
			"product_flag",
			"skuv"
		);
		
		foreach($attributesToSelect as $attribute){
			$collection->joinAttribute($attribute, 'catalog_product/'.$attribute, 'entity_id', null, 'left', $storeId);
		}
		
		$neededAttributes = array(
			"name"
		);
		
		foreach($neededAttributes as $attribute){
			$collection->joinAttribute($attribute, 'catalog_product/'.$attribute, 'entity_id', null, 'inner', $storeId);
		}
		
		$select = $collection->getSelect();
		$adapter = $select->getAdapter();
		
		
		// Join stock index
		$collection->getSelect()->joinLeft(
			array("stock"=>$collection->getTable('cataloginventory/stock_status_indexer_idx')),
			$adapter->quoteInto("stock.product_id=e.entity_id AND stock.website_id=?", $store->getWebsiteId()),
			array("stock_status"=>"stock.stock_status", "stock_qty"=>"stock.qty")
		);
		
		
		// Join prices data
		$joinCond = array(
			'price_index.entity_id = e.entity_id',
			$adapter->quoteInto('price_index.website_id = ?', $store->getWebsiteId()),
			// Default not logged in
			$adapter->quoteInto('price_index.customer_group_id = ?', Mage_Customer_Model_Group::NOT_LOGGED_IN_ID)
		);

		$least = $adapter->getLeastSql(
			array('price_index.min_price', 'price_index.tier_price')
		);
		$minimalExpr = $adapter->getCheckSql(
				'price_index.tier_price IS NOT NULL', $least, 'price_index.min_price'
		);

		$colls = array(
			'price', 
			'tax_class_id', 
			'final_price',
			'minimal_price' => $minimalExpr , 
			'min_price', 
			'max_price', 
			'tier_price'
		);

		$tableName = array('price_index' => $collection->getTable('catalog/product_index_price'));

		$select->join($tableName, implode(' AND ', $joinCond), $colls);
		

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
			$item['entity_id'] = (int)$item['entity_id'];
			$item['id'] = (int)$item['entity_id'];
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



