<?php

class Zolago_Rma_Model_Resource_Rma_Collection extends ZolagoOs_Rma_Model_Mysql4_Rma_Collection
{
	
	protected $_productJoined;
	protected $_orderJoined;
	
	public function addCustomerName() {
		return $this;
	}
	
	public function addItemsData(){
		$this->setFlag('add_items_data', true);
		return $this;
	}
	
	/**
	 * @return Zolago_Rma_Model_Resource_Rma_Collection
	 */
	public function addCustomerNames() {
		return $this->_joinOrderTable();
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
		
		$this->_joinRmaItem();
		
		$where = array(
			$select->getAdapter()->quoteInto("rma_item.name LIKE ?", "%".$productName."%"),
			$select->getAdapter()->quoteInto("rma_item.sku LIKE ?", "%".$productName."%"),
			$select->getAdapter()->quoteInto("rma_item.vendor_sku LIKE ?", "%".$productName."%"),
			$select->getAdapter()->quoteInto("rma_item.vendor_simple_sku LIKE ?", "%".$productName."%"),
		);
		$this->getSelect()->where(join(" OR ", $where));
		return $this;
	}
	
	
	/**
	 * @param type $conditoins
	 * @return \Zolago_Rma_Model_Resource_Rma_Collection
	 * 
	 */
	public function addItemConditionFilter($conditoins) {
		if(is_array($conditoins) && $conditoins){
			$this->_joinRmaItem();
			$this->getSelect()->where("rma_item.item_condition IN (?)", $conditoins);
		}
		return $this;
	}


	
	/**
	 * Load grid items data
	 * @return type
	 */
    protected function _afterLoad() {
		$return = parent::_afterLoad();
		if($this->getFlag("add_items_data")){
			$ids = array_keys($this->getItems());
			$collection = Mage::getResourceModel("urma/rma_item_collection");
			$collection->addFieldToFilter("main_table.parent_id", array("in"=>$ids));
			$grouped = array();
			foreach($collection as $item){
				$parentId = $item->getParentId();
				if(!isset($grouped[$parentId])){
					$grouped[$parentId] = array();
				}
				$grouped[$parentId][] = $item;
			}
			foreach($grouped as $poId=>$items){
				$this->getItemById($poId)->setRmaItems($items);
			}
		}
		
        return $return;
    }
	
	
	protected function _joinRmaItem() {
        if (!$this->_productJoined) {
            $this->getSelect()->join(
                array('rma_item'=>$this->getTable('urma/rma_item')),
                'rma_item.parent_id=main_table.entity_id',
				array()
            );
			$this->getSelect()->group("main_table.entity_id");
            $this->_productJoined = true;
        }
        return $this;
        
    }
	protected function _joinOrderTable()
    {
        if (!$this->_orderJoined) {
            $this->getSelect()->join(
                array('order_table'=>$this->getTable('sales/order')),
                'order_table.entity_id=main_table.order_id',
                array(
					'order_table.base_currency_code', 
					'order_customer_email' => "order_table.customer_email", 
					'order_table.customer_firstname', 
					'order_table.customer_lastname', 
					'customer_fullname'=>new Zend_Db_Expr("CONCAT_WS(' ', order_table.customer_firstname, order_table.customer_lastname)"))
            );
            $this->_orderJoined = true;
        }
        return $this;
    }
	
	
}
