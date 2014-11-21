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
		$id = $this->getRequest()->getParam('sizetable_id');
		if(!$id) {
			return false;
		} else {
			//load $sizetable and return it
			return array('sizetable_id'=>$id,'name' => 'dupa', 'sizetable' => array(1=>'default',2=>'optional'));
		}
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/save");
	}
}