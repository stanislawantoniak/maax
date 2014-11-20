<?php
class Zolago_Sizetable_Block_Dropship_Sizetable_Edit extends Mage_Core_Block_Template {
	private $stores;
	private $defaultStore;

	public function __construct() {
		$stores = array();
		foreach(Mage::app()->getStores() as $store) {
			if($store->getStoreId() == 1)
				$this->defaultStore = $store->getData();
			else
				$stores[] = $store->getData();
		}
		$this->stores = $stores;
	}

	public function getOtherStores() {
		return $this->stores;
	}

	public function getDefaultStore() {
		return $this->defaultStore;
	}

	public function getSizeTable() {
		return false;
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/save");
	}
}