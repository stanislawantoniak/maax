<?php
class Zolago_Po_Model_Resource_Po_Collection 
	extends ZolagoOs_OmniChannelPo_Model_Mysql4_Po_Collection
{
	
	protected $_vendorId;
	protected $_vendorJoined = false;
	protected $_productJoined = false;
	protected $_allocationsJoined = false;
	protected $_transactionsJoined = false;
	
	public function addAlertFilter($int) {
		$this->getSelect()->where("main_table.alert & ".(int)$int);
		return $this;
	}
	
    public function addOrderData() {
		return $this->_joinOrderTable();
	}
	public function addProductData() {
	    return $this->_joinPoItem();
	}
	public function addVendorData() {
	    return $this->_joinVendorTable();
	}
	public function addPaymentStatuses() {
		if (Mage::helper('zolagopayment')->getConfigUseAllocation()){
			// use allocation logic
			return $this->_joinAllocationsTable();
		}

		// simplified logic
		return $this->_joinTransactionsTable();
	}

/*	public function getAllIds($limit = null, $offset = null) {
		if($this->_allocationsJoined) {
			$this->addFieldToSelect("IF(SUM(allocation.allocation_amount)>=main_table.grand_total_incl_tax,1,0) AS `payment_status`");
		}
		parent::getAllIds();
	}*/

	/**
	 * Create all ids retrieving select with limitation
	 * Backward compatibility with EAV collection
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return Mage_Eav_Model_Entity_Collection_Abstract
	 */
	protected function _getAllIdsSelect($limit = null, $offset = null)
	{
		$idsSelect = clone $this->getSelect();
		$idsSelect->reset(Zend_Db_Select::ORDER);
		$idsSelect->reset(Zend_Db_Select::LIMIT_COUNT);
		$idsSelect->reset(Zend_Db_Select::LIMIT_OFFSET);
		if (Mage::helper('zolagopayment')->getConfigUseAllocation()){
			// use allocation logic
			if(!$this->_allocationsJoined) {
				$idsSelect->reset(Zend_Db_Select::COLUMNS);
			}
		} else {
			// simplified logic
			if(!$this->_transactionsJoined) {
				$idsSelect->reset(Zend_Db_Select::COLUMNS);
			}
		}

		$idsSelect->columns($this->getResource()->getIdFieldName(), 'main_table');
		$idsSelect->limit($limit, $offset);
		return $idsSelect;
	}

	/**
	 * Retrive all ids for collection
	 * Backward compatibility with EAV collection
	 *
	 * @param int $limit
	 * @param int $offset
	 * @return array
	 */
	public function getAllIds($limit = null, $offset = null)
	{
		return $this->getConnection()->fetchCol(
			$this->_getAllIdsSelect($limit, $offset),
			$this->_bindParams
		);
	}

	public function addVendorFilter($vendor) {
		if($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor){
			$vendor = $vendor->getId();
		}
		$this->_vendorId = $vendor;
		$this->addFieldToFilter("main_table.udropship_vendor", $vendor);
		return $this;
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
	
	public function addSameEmailPo() {
		$this->setFlag("add_same_email_po", true);
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
		if($this->getFlag("add_same_email_po") && $this->_vendorId){
			$ids = array_keys($this->getItems());
			
			$adapter = $this->getSelect()->getAdapter();
			$select = $adapter->select();
			
			$select->from(
				array("main_po"=>$this->getTable('udpo/po')),
				array(
					"main_po_id"		=> "main_po.entity_id",
					"entity_id"			=> "same_po.entity_id",
					"increment_id"		=> "same_po.increment_id",
				)
			);

			$statModel = Mage::getSingleton("zolagopo/po_status");
			$finishedStatuses = $statModel::getFinishStatuses();
			
			$conds = array(
				"same_po.udropship_vendor = main_po.udropship_vendor",
				"same_po.customer_email = main_po.customer_email",
				"same_po.entity_id != main_po.entity_id",
				"same_po.udropship_status NOT IN (".implode(",", $finishedStatuses).")"	
			);
			
			$select->join(
					array("same_po"=>$collection->getTable('udpo/po')),
					implode(" AND ", $conds),
					array("increment_id")
			);
			
			
			$select->where("main_po.udropship_vendor=?", $this->_vendorId);
			$select->where("main_po.entity_id IN (?)", $ids);
			$select->where("main_po.udropship_status NOT IN (?)", $finishedStatuses);
			
			$grouped = array();
			foreach($adapter->fetchAll($select) as $row){
				$poId = $row['main_po_id'];
				if(!isset($grouped[$poId])){
					$grouped[$poId] = array();
				}
				$grouped[$poId][] = new Varien_Object($row);
			}
			foreach($grouped as $poId=>$poArray){
				$this->getItemById($poId)->setSameEmailPo($poArray);
			}
		}
        return $return;
    }
    protected function _joinVendorTable() {
        if (!$this->_vendorJoined) {
            $this->getSelect()->join(
				array('vendor_table'=>$this->getTable('udropship/vendor')),
                'vendor_table.vendor_id=main_table.udropship_vendor',
                array ('vendor_table.vendor_name')
            );
            $this->_vendorJoined = true;
        }
        return $this;
        
    }
    protected function _joinPoItem() {
        if (!$this->_productJoined) {
            $this->getSelect()->join(
                array('item_table'=>$this->getTable('udpo/po_item')),
                'item_table.parent_id=main_table.entity_id'
                ,
                array(
                    'item_table.qty'                    
                )
            );
            $this->_orderJoined = true;
        }
        return $this;
        
    }

	/**
	 * @return $this
	 */
	protected function _joinTransactionsTable()
	{
		if (!$this->_transactionsJoined) {
			$this->getSelect()->joinLeft(
				array("transaction" => $this->getTable("sales/payment_transaction")),
				"transaction.order_id = main_table.order_id",
				"IF(SUM(transaction.txn_amount)>=main_table.grand_total_incl_tax,1,0) AS payment_status"
			);
			$this->_transactionsJoined = true;
		}
		return $this;
	}

	/**
	 * @return $this
	 */
	protected function _joinAllocationsTable()
	{
		if (!$this->_allocationsJoined) {
			$this->getSelect()->joinLeft(
				array("allocation" => $this->getTable("zolagopayment/allocation")),
				"allocation.po_id = main_table.entity_id AND allocation.allocation_type = '" . Zolago_Payment_Model_Allocation::ZOLAGOPAYMENT_ALLOCATION_TYPE_PAYMENT . "'",
				"IF(SUM(allocation.allocation_amount)>=main_table.grand_total_incl_tax,1,0) AS payment_status"
			);
			$this->_allocationsJoined = true;
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

    /**
     * Add POS data to collection
     *
     * @param string $cols
     * @return $this
     */
    public function addPosData($cols = '*') {
        $select = $this->getSelect();

        $select->joinLeft(
            array("zolagopos"=>$this->getTable('zolagopos/pos')),
            "zolagopos.pos_id = main_table.default_pos_id",
            array($cols)
        );

        return $this;
    }
}
