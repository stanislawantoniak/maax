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
				
				$select = $collection->getSelect();
				foreach($this->_getRestQuery() as $key=>$value){
					$collection->addAttributeToFilter($key, $value);
				}
				
				Mage::log($collection->getSelect()."");

				
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
	 * @return int
	 */
	protected function _getStoreId() {
		return $this->getRequest()->getParam("store_id");
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
		
		$storeId = $this->_getStoreId();
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
			"product_flag",
			"status",
			"special_price",
			"price",
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
		
		
		$stockTable = $collection->getTable('cataloginventory/stock_item');
		$linkTabel = $collection->getTable("catalog/product_super_link");
		
		
		// Join price attrib
		$priceExpression = $adapter->getCheckSql(
			'0', // @todo after new attribure add
			 $adapter->getCheckSql("at_special_price.value_id>0", "at_special_price.value", "at_special_price_default.value"),
			 $adapter->getCheckSql("at_price.value_id>0", "at_price.value", "at_price_default.value")
		);
		
		$collection->addExpressionAttributeToSelect('display_price', $priceExpression, array());
		
		// Join stock item
		$collection->joinTable(
				$stockTable, 
				'product_id=entity_id',
				array('is_in_stock'=>'is_in_stock'), 
				$adapter->quoteInto("{{table}}.stock_id=?", Mage_CatalogInventory_Model_Stock::DEFAULT_STOCK_ID),
				'left'
		);
		
		
		// Join all childs
		$subSelect = $adapter->select();
		$subSelect->from(array("link_all"=>$linkTabel), array("COUNT(link_all.link_id)"));
		$subSelect->where("link_all.parent_id=e.entity_id");
	
		$collection->addExpressionAttributeToSelect('all_child_count', $subSelect, array());
		
		// Join available child count
		$subSelect = $adapter->select();
		$subSelect->from(array("link_available"=>$linkTabel), array("COUNT(link_available.link_id)"));
		$subSelect->join(
				array("child_stock_available"=>$stockTable), 
				"link_available.product_id=child_stock_available.product_id", 
				array());
		$subSelect->where("link_available.parent_id=e.entity_id");
		$subSelect->where("child_stock_available.is_in_stock=?",1);
		$collection->addExpressionAttributeToSelect('available_child_count', 
				"IF(e.type_id IN ('configurable', 'grouped'), (".$subSelect."), null)", array());
		
		// Join child qtys
		$subSelect = $adapter->select();
		$subSelect->from(array("link_qty"=>$linkTabel), array("SUM(child_qty.qty)"));
		$subSelect->join(
				array("child_qty"=>$stockTable), 
				"link_qty.product_id=child_qty.product_id", 
				array());
		$subSelect->where("link_qty.parent_id=e.entity_id");
		$subSelect->where("child_qty.is_in_stock=?",1);
		// Use subselect only for parent products
		$collection->addExpressionAttributeToSelect('stock', 
				"IF(e.type_id IN ('configurable', 'grouped'), (".$subSelect."), $stockTable.qty)", array());
		
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
		//$totalSelect->resetJoinLeft();
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
			$item['campaign_regular_id'] = "Lorem ipsum dolor sit manet"; /** @todo impelemnt **/
			$item['store_id'] = $collection->getStoreId();
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
	protected function _getEditableAttributes() {
		return array(
			"display_price", 
			"converter_price_type", 
			"is_new", 
			"is_bestseller", 
			"product_flag", 
			"is_in_stock",
			"status"
		);
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableQueryParams() {
		return array(
			"name", 
			"display_price", 
			"converter_price_type", 
			"price_margin", 
			"msrp", 
			"is_new", 
			"is_bestseller", 
			"product_flag",
			"is_in_stock",
			"available_child_count",
			"stock",
			"status",
			"type_id",
		);
	}
	
	/**
	 * @return array
	 */
	protected function _getAvailableSortParams() {
		return array_merge($this->_getAvailableQueryParams(), array(
			"display_price", 
			"converter_price_type", 
			"price_margin", 
			"msrp", 
			"is_new",
			"is_bestseller",
			"product_flag",
			"is_in_stock",
			"available_child_count",
			"stock",
			"status",
			"type_id",
		));
	}

}



