<?php
class Zolago_Po_Model_Resource_Po_Collection 
	extends Unirgy_DropshipPo_Model_Mysql4_Po_Collection
{
    public function addOrderData() {
		return $this->_joinOrderTable();
	}
	
	/**
	 * @param string $customerName
	 * @return Zolago_Po_Model_Resource_Po_Collection
	 */
	public function addCustomerNameFilter($customerName) {
		$adapter = $this->getSelect()->getAdapter();
		$where = array(
			$adapter->quoteInto("order_table.customer_firstname LIKE ?", '%'.$customerName.'%'),
			$adapter->quoteInto("order_table.customer_lastname LIKE ?", '%'.$customerName.'%'),
			$adapter->quoteInto("order_table.customer_email LIKE ?", '%'.$customerName.'%'),
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
		$select->group("main_table.entity_id");
		$this->getSelect()->where("po_item.name LIKE ?", "%".$productName."%");
		return $this;
	}
	
	public function addProductNames() {
		$this->setFlag("add_po_items_data", true);
		return $this;
	}
	
	/**
	 * Load grid items data
	 * @return type
	 */
    protected function _afterLoad() {
		$return = parent::_afterLoad();
		if($this->getFlag("add_po_items_data")){
			$select = $this->_conn->select();
			$select->from(
					array("po_item"=>$this->getTable('udpo/po_item')), 
					array("po_item.parent_id", "po_item.name")
			);
			$select->where("po_item.parent_id IN (?)", $this->getAllIds());
			$grouped = array();
			foreach($this->_conn->fetchAll($select) as $row){
				$parentId = $row['parent_id'];
				if(!isset($grouped[$parentId])){
					$grouped[$parentId] = array();
				}
				$grouped[$parentId][] = $row['name'];
			}
			foreach($grouped as $itemId=>$names){
				$this->getItemById($itemId)->setProductNames($names);
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
