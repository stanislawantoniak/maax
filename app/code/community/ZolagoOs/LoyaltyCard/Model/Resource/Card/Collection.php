<?php

class ZolagoOs_LoyaltyCard_Model_Resource_Card_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct() {
		parent::_construct();
		$this->_init("zosloyaltycard/card");
	}

	/**
	 * @param ZolagoOs_OmniChannel_Model_Vendor | int $vendor
	 * @return $this
	 */
	public function addVendorFilter($vendor) {
		if ($vendor instanceof ZolagoOs_OmniChannel_Model_Vendor) {
			$vendor = $vendor->getId();
		}
		$this->addFieldToFilter('vendor_id', (int)$vendor);
		return $this;
	}
}
