<?php
class Zolago_Catalog_Model_Resource_Vendor_Price_Collection 
	extends Zolago_Catalog_Model_Resource_Vendor_Collection_Abstract
{
  
	public function addAttributes() {
		
		// Add non-o attribs
		$attributesToSelect = array(
			"price_margin",
			"campaign_regular_id",
			"msrp",
			"product_flag",
			"status",
			"special_price",
			"price",
			"skuv"
		);
		
		foreach($attributesToSelect as $attribute){
			$this->joinAttribute($attribute, 'catalog_product/'.$attribute, 'entity_id', null, 'left', $this->getStoreId());
		}
		
		$boolAttributes = array(
			"is_new",
			"is_bestseller",
			"converter_price_type",
			"converter_msrp_type"
		);
				
		foreach($boolAttributes as $attribute){
			$this->addExpressionAttributeToSelect($attribute, "IFNULL({{attribute}}, 0)", $attribute);
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
		$subSelect->from(array("link_qty"=>$linkTabel), array("IFNULL(SUM(child_qty.qty),0)"));
		$subSelect->join(
				array("child_qty"=>$stockTable), 
				"link_qty.product_id=child_qty.product_id", 
				array());
		$subSelect->where("link_qty.parent_id=e.entity_id");
		$subSelect->where("child_qty.is_in_stock=?",1);
		// Use subselect only for parent products
		$this->addExpressionAttributeToSelect('stock_qty', 
				"IF(e.type_id IN ('configurable', 'grouped'), (".$subSelect."), $stockTable.qty)", array());
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getEditableAttributes() {
		return array(
			"is_new", 
			"is_bestseller", 
			"status",
			"converter_price_type",
			"converter_msrp_type",
			"price_margin"
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
			"converter_msrp_type",
			"price_margin", 
			"msrp", 
			"is_new", 
			"is_bestseller", 
			"product_flag",
			"is_in_stock",
			"available_child_count",
			"stock_qty",
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
			"converter_msrp_type", 
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


