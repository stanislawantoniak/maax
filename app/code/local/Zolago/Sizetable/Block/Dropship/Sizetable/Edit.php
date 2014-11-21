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
	$id = $this->getRequest()->getParam('sizetable_id');
		if($id === null) {
			return false;
		} else {
			$sizetable = Mage::getModel("zolagosizetable/sizetable")->load($id);
			return $sizetable->getData();
		}
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/save");
	}
}