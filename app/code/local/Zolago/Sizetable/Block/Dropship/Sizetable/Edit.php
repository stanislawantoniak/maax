<?php
class Zolago_Sizetable_Block_Dropship_Sizetable_Edit extends Mage_Core_Block_Template {
	protected $stores;

	public function __construct() {
		$stores = array();
		foreach(Mage::app()->getStores() as $store) {
			$stores[] = $store->getData();
		}
		$this->stores = $stores;
	}

	public function getStores() {
		return $this->stores;
	}

	public function getSizeTable() {
		$sizetable = Mage::registry("sizetable");
		return Mage::registry("sizetable");
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/save");
	}
}