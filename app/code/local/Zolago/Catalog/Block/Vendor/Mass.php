<?php

class Zolago_Catalog_Block_Vendor_Mass extends Mage_Core_Block_Template
{
    public function getGridHtml() {
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$block = $this->getLayout()->createBlock("zolagocatalog/vendor_mass_grid");
		$html = $block->toHtml();
		$design->setArea("frontend");
		return $html;
	}
    public function getSwitcherHtml() {
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$block = $this->getLayout()->createBlock("adminhtml/store_switcher");
		$block->setUseConfirm(0);
		$html = $block->toHtml();
		$design->setArea("frontend");
		return $html;
	}
	public function getCurrentAttributeSet() {
		return Mage::app()->getRequest()->getParam("attribute_set");
	}
}