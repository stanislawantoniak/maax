<?php

/**
 * Class GH_Api_Model_Resource_Message_Collection
 * @method GH_Api_Model_Resource_Message_Collection addFieldToFilter(string $field, string $value)
 * @method GH_Api_Model_Message getFirstItem()
 */
class GH_Api_Model_Resource_Message_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct()
	{
		parent::_construct();
		$this->_init('ghapi/message');
	}

	public function filterByPoIncrementId($poIncrementId) {
		return $this->addFieldToFilter('po_increment_id',$poIncrementId);
	}

	public function filterByVendorId($vendorId) {
		return $this->addFieldToFilter('vendor_id',$vendorId);
	}
	public function filterByOrderId($orderId) {
	    return $this->addFieldToFilter('po_increment_id',$orderId);
	}
	public function filterByMessage($message) {
		return $this->addFieldToFilter('message',$message);
	}

	public function filterByStatus($status) {
		return $this->addFieldToFilter('status',$status);
	}

	public function filterByIds(array $messages) {
		return $this->addFieldToFilter('message_id',array('in'=>$messages));
	}
}