<?php

class Zolago_Catalog_Block_Vendor_Product_Attributesetswitcher extends Mage_Core_Block_Template {
	public function getAttributeSets() {
			$array = Mage::getResourceSingleton('zolagocatalog/vendor_mass')
				->getAttributeSetsForVendor($this->getVendor()
		);
		return $array;
	}
	public function getAttributeSetId() {
		return $this->getParentBlock()->getAttributeSetId();
	}
	public function getChangeUrl() {
		return $this->getUrl("*/*/*");
	}
	public function getVendor() {
		return Mage::getModel("udropship/session")->getVendor();
	}
}