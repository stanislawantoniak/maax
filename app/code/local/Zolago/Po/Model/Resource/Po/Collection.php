<?php
class Zolago_Po_Model_Resource_Po_Collection 
	extends Unirgy_DropshipPo_Model_Mysql4_Po_Collection
{
    public function addOrderData() {
		return $this->_joinOrderTable();
	}
	
	public function addHasShipment() {
		$this->getSelect()->joinLeft(
				array("shipment"=>$this->getTable("sales/shipment")), 
				"shipment.udpo_id=main_table.entity_id",
				array("has_shipment"=>$this->_getShipmentExpr())
		);
		$this->getSelect()->group("main_table.entity_id");
	}
	
	
	protected function _getShipmentExpr(){
		return new Zend_Db_Expr("COUNT(shipment.entity_id)>0");
	}
	
	public function joinAggregatedNames() {
		$select = $this->getSelect();
		
		$select->joinLeft(
				array("aggregated"=>$this->getTable('zolagopo/aggregated')), 
				"aggregated.aggregated_id=main_table.aggregated_id",
			    array("aggregated_name")
		);
		
		return $this;
	}

	/**
	 * @param string $customerName
	 * @return Zolago_Po_Model_Resource_Po_Collection
	 */
	public function addCustomerNameFilter($customerName) {
		$adapter = $this->getSelect()->getAdapter();
		$where = array(
			$adapter->quoteInto("order_table.customer_email LIKE ?", '%'.$customerName.'%'),
			// Fullname search
			$adapter->quoteInto("CONCAT_WS(' ', order_table.customer_firstname, order_table.customer_lastname) LIKE ?", 
					'%'.$customerName.'%'),
		);
		$this->getSelect()->where(join(" OR ", $where));
		return $this;
	}
	
	/**
	 * @param string $productName
	 * @return Zolago_Po_Model_Resource_Po_Collection
	 */
	public function addProductNameFilter($productName) {
		$select = $this->getSelect();
		
		$select->join(
				array("po_item"=>$this->getTable('udpo/po_item')), 
				"po_item.parent_id=main_table.entity_id",
			    array()
		);
		$select->join(
				array("order_item"=>$this->getTable('sales/order_item')), 
				$select->getAdapter()->quoteInto("order_item.item_id=po_item.order_item_id AND order_item.product_type IN(?)", $this->_getVisibleTypes()),
			    array()
		);
		$select->group("main_table.entity_id");
		$where = array(
			$select->getAdapter()->quoteInto("po_item.name LIKE ?", "%".$productName."%"),
			$select->getAdapter()->quoteInto("po_item.sku LIKE ?", "%".$productName."%"),
		);
		$this->getSelect()->where(join(" OR ", $where));
		return $this;
	}


	public function addProductNames() {
		$this->setFlag("add_po_items_data", true);
		return $this;
	}
	public function getSelectCountSql() {
        $this->_renderFilters();
        $countSelect = clone $this->getSelect();
		$countSelect->reset();
		$countSelect->from($this->getSelect(), array("COUNT(*)"));
		return $countSelect;
	}
	
	public function addHasShipmentFilter($hasShipment) {
		$oprator = $hasShipment ?  ">" : "=";
		$this->getSelect()->having("COUNT(shipment.entity_id)".$oprator."0");
	}
	
	/**
	 * @return array
	 */
	protected function _getVisibleTypes() {
		return array("simple", "virual");
	}


	/**
	 * Load grid items data
	 * @return type
	 */
    protected function _afterLoad() {
		$return = parent::_afterLoad();
		if($this->getFlag("add_po_items_data")){
			$ids = array_keys($this->getItems());
			$collection = Mage::getResourceModel("zolagopo/po_item_collection");
			$collection->addFieldToFilter("main_table.parent_id", array("in"=>$ids));
			$collection->addFieldToFilter("main_table.parent_item_id", array("null"=>true));
			$grouped = array();
			foreach($collection as $item){
				$parentId = $item->getParentId();
				if(!isset($grouped[$parentId])){
					$grouped[$parentId] = array();
				}
				$grouped[$parentId][] = $item;
			}
			foreach($grouped as $poId=>$items){
				$this->getItemById($poId)->setOrderItems($items);
			}
		}
        return $return;
    }
	protected function _joinOrderTable()
    {
        if (!$this->_orderJoined) {
            $this->getSelect()->join(
                array('order_table'=>$this->getTable('sales/order')),
                'order_table.entity_id=main_table.order_id',
                array(
					'order_table.base_currency_code', 
					'order_table.customer_email', 
					'order_table.customer_firstname', 
					'order_table.customer_lastname', 
					'customer_fullname'=>new Zend_Db_Expr("CONCAT_WS(' ', order_table.customer_firstname, order_table.customer_lastname)"))
            );
            $this->_orderJoined = true;
        }
        return $this;
    }
}
