<?php

class Zolago_Catalog_Block_Vendor_Mass_Attributesetswitcher extends Mage_Core_Block_Template {
	public function getAttributeSets() {
		return array(""=>"", 1=>"Some attr set 1", 2=>"Some attr set 2");
	}
	public function getCurrentAttributeSet() {
		return Mage::app()->getRequest()->getParam("attribute_set");
	}
	public function getChangeUrl() {
		return $this->getUrl("*/*/*");
	}
}