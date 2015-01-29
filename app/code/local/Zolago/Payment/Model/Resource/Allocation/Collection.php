<?php

class Zolago_Payment_Model_Resource_Allocation_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
	private $_posJoined = false;
  
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
		$this->getSelect()
			->join(
				'sales_payment_transaction',
				'main_table.transaction_id =sales_payment_transaction.transaction_id',
				array('sales_payment_transaction.txn_id'));
		return $this;
	}

	public function joinOperators() {
		$this->getSelect()
			->joinLeft(
			'zolago_operator',
			'main_table.operator_id =zolago_operator.operator_id',
			array(
/*				'zolago_operator.firstname',
				'zolago_operator.lastname'*/
				'zolago_operator.email as operator_email'
			));
		return $this;
	}

	public function joinPos() {
		if(!$this->_posJoined) {
			$this->getSelect()
				->joinLeft(
					'udropship_po',
					'main_table.po_id = udropship_po.entity_id',
					array('udropship_po.increment_id AS increment_id')
				);
			$this->_posJoined = true;
		}
		return $this;
	}

    public function joinVendors() {
        $this->getSelect()
            ->joinLeft(
                'udropship_vendor',
                'main_table.vendor_id = udropship_vendor.vendor_id',
                array(
                    'udropship_vendor.email as vendor_email'
                ));
        return $this;
    }
}
