<?php

class ZolagoOs_LoyaltyCard_Model_Resource_Card_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract {

	protected function _construct() {
		parent::_construct();
		$this->_init("zosloyaltycard/card");
	}

	/**
	 * Unserialize additional_information in each item
	 *
	 * @return $this
	 */
	protected function _afterLoad() {
		foreach ($this->_items as $item) {
			$this->getResource()->unserializeFields($item);
			$item->addData($item->getAdditionalInformation());
		}
		return parent::_afterLoad();
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

	/**
	 * @param Mage_Core_Model_Store|int $store
	 * @return $this
	 */
	public function addStoreFilter($store) {
		if ($store instanceof Mage_Core_Model_Store) {
			$store = $store->getId();
		}
		$this->addFieldToFilter('store_id', (int)$store);
		return $this;
	}

	/**
	 * @param string|array $numbers
	 * @return $this
	 */
	public function addCardNumberFilter($numbers) {
		if (!is_array($numbers)) {
			$numbers = array($numbers);
		}
		$this->addFieldToFilter('card_number', array("in" => $numbers));
		return $this;
	}

	/**
	 * @param string|array $types
	 * @return $this
	 */
	public function addCardTypeFilter($types) {
		if (!is_array($types)) {
			$types = array($types);
		}
		$this->addFieldToFilter('card_type', array("in" => $types));
		return $this;
	}
}
