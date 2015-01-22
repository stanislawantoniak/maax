<?php

class Zolago_Payment_Model_Resource_Allocation_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract {
  
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
}
