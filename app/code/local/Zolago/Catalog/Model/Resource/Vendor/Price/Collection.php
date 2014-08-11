<?php
class Zolago_Catalog_Model_Resource_Vendor_Price_Collection 
	extends Mage_Catalog_Model_Resource_Product_Collection
{
  
	public function addAttributes() {
		
		// Add non-o attribs
		$attributesToSelect = array(
			"converter_price_type",
			"price_margin",
			"campaign_regular_id",
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
			$this->joinAttribute($attribute, 'catalog_product/'.$attribute, 'entity_id', null, 'left', $this->getStoreId());
		}
		
		$neededAttributes = array(
			"name"
		);
		
		foreach($neededAttributes as $attribute){
			$this->joinAttribute($attribute, 'catalog_product/'.$attribute, 'entity_id', null, 'inner', $this->getStoreId());
		}
	}
	
	public function joinAdditionalData() {
			
		$select = $this->getSelect();
		$adapter = $select->getAdapter();
		
		
		$stockTable = $this->getTable('cataloginventory/stock_item');
		$linkTabel = $this->getTable("catalog/product_super_link");
		
		// Join price attrib
		$priceExpression = $adapter->getCheckSql(
			'(at_campaign_regular_id.value IS NOT NULL) AND (at_campaign_regular_id.value > 0)', 
			 $adapter->getCheckSql("at_special_price.value_id>0", "at_special_price.value", "at_special_price_default.value"),
			 $adapter->getCheckSql("at_price.value_id>0", "at_price.value", "at_price_default.value")
		);
		
		$this->addExpressionAttributeToSelect('display_price', $priceExpression, array());
		
		
		// Join stock item
		$this->joinTable(
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
	
		$this->addExpressionAttributeToSelect('all_child_count', $subSelect, array());
		
		// Join available child count
		$subSelect = $adapter->select();
		$subSelect->from(array("link_available"=>$linkTabel), array("COUNT(link_available.link_id)"));
		$subSelect->join(
				array("child_stock_available"=>$stockTable), 
				"link_available.product_id=child_stock_available.product_id", 
				array());
		$subSelect->where("link_available.parent_id=e.entity_id");
		$subSelect->where("child_stock_available.is_in_stock=?",1);
		$this->addExpressionAttributeToSelect('available_child_count', 
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
		$this->addExpressionAttributeToSelect('stock', 
				"IF(e.type_id IN ('configurable', 'grouped'), (".$subSelect."), $stockTable.qty)", array());
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function prepareRestResponse(array $sort, array $range) {
		
		$collection = $this;
		
		$select = $collection->getSelect();
		/* @var $select Varien_Db_Select */
		
		if($sort){
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
			//$item['campaign_regular_id'] = "Lorem ipsum dolor sit manet"; /** @todo impelemnt **/
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
	public function getEditableAttributes() {
		return array(
			"display_price", 
			"price_margin",
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
	public function getAvailableQueryParams() {
		return array(
			"name", 
			"display_price", 
			"campaign_regular_id",
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
	public function getAvailableSortParams() {
		return array_merge($this->getAvailableQueryParams(), array(
			"display_price", 
			"campaign_regular_id",
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


