<?php

class Zolago_Payment_Model_Resource_Allocation_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	private $_posJoined = false;
	private $_transactionsJoined = false;
	private $_refundTransactionsJoined = false;
	private $_operatorsJoined = false;
	private $_vendorsJoined = false;
	private $_customersJoined = false;
	private $_rmasJoined = false;
  
    protected function _construct() {
        parent::_construct();
        $this->_init('zolagopayment/allocation');
    }

	public function addPoIdFilter($po_id) {
		return $this->addFieldToFilter('po_id',$po_id);
	}

	public function addAllocationTypeFilter($type) {
		return $this->addFieldToFilter('allocation_type',$type);
	}

	public function addCustomerIdFilter($customerId) {
		return $this->addFieldToFilter('customer_id',$customerId);
	}

	public function joinTransactions() {
		if(!$this->_transactionsJoined) {
			$this->getSelect()
				->join(
					'sales_payment_transaction',
					'main_table.transaction_id =sales_payment_transaction.transaction_id',
					array('sales_payment_transaction.txn_id'));
			$this->_transactionsJoined = true;
		}
		return $this;
	}

	public function joinRefundTransactions() {
		if(!$this->_refundTransactionsJoined) {
			$this->getSelect()
				->joinLeft(
					'sales_payment_transaction as refund_transactions',
					'main_table.refund_transaction_id = refund_transactions.transaction_id',
					array('refund_transactions.txn_id as refund_transaction_txn_id'));
			$this->_refundTransactionsJoined = true;
		}
		return $this;
	}

	public function joinOperators() {
		if(!$this->_operatorsJoined) {
			$this->getSelect()
				->joinLeft(
					'zolago_operator',
					'main_table.operator_id =zolago_operator.operator_id',
					array(
						/*				'zolago_operator.firstname',
										'zolago_operator.lastname'*/
						'zolago_operator.email as operator_email'
					));
			$this->_operatorsJoined = true;
		}
		return $this;
	}

	public function joinPos() {
		if(!$this->_posJoined) {
			$this->getSelect()
				->joinLeft(
					'udropship_po',
					'main_table.po_id = udropship_po.entity_id',
					array('udropship_po.increment_id')
				);
			$this->_posJoined = true;
		}
		return $this;
	}

    public function joinVendors() {
	    if(!$this->_vendorsJoined) {
		    $this->getSelect()
			    ->joinLeft(
				    'udropship_vendor',
				    'main_table.vendor_id = udropship_vendor.vendor_id',
				    array(
					    'udropship_vendor.email as vendor_email',
					    'udropship_vendor.vendor_name'
				    ));
		    $this->_vendorsJoined = true;
	    }
        return $this;
    }

	public function joinCustomers() {
		if(!$this->_customersJoined) {
			$this->getSelect()
				->joinLeft(
					'customer_entity',
					'main_table.customer_id = customer_entity.entity_id',
					array(
						'customer_entity.email as customer_email'
					));
			$this->_customersJoined = true;
		}
		return $this;
	}

	public function joinRmas() {
		if(!$this->_rmasJoined) {
			$this->getSelect()
				->joinLeft(
					'urma_rma',
					'main_table.rma_id = urma_rma.entity_id',
					array(
						'urma_rma.increment_id as rma_increment_id'
					));
			$this->_rmasJoined = true;
		}

		return $this;
	}
}
