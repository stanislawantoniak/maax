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
		if(!$id) {
			return false;
		} else {
			//todo: load $sizetable and return it
			return array('sizetable_id'=>$id,'name' => 'dupa', 'sizetable' => array(1=>'default',2=>'optional'));
		}
	}

	public function getAction() {
		return $this->getUrl("udropship/sizetable/save");
	}
}