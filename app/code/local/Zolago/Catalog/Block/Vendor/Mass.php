<?php

class Zolago_Catalog_Block_Vendor_Mass extends Mage_Core_Block_Template
{
	public function _prepareLayout() {
		$this->_prepareGrid();
		$this->_prepareStoreSwitcher();
		parent::_prepareLayout();
	}
	
    public function _prepareGrid() {
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$block = $this->getLayout()->createBlock("zolagocatalog/vendor_mass_grid");
		$this->setGridHtml($block->toHtml());
		$this->setGrid($block);
		$design->setArea("frontend");
	}
    public function _prepareStoreSwitcher() {
		if(Mage::app()->isSingleStoreMode()){
			return;
		}
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$block = $this->getLayout()->createBlock("adminhtml/store_switcher");
		$block->setUseConfirm(0);
		$this->setStoreSwitcherHtml($block->toHtml());
		$this->setStoreSwitcher($block);
		$design->setArea("frontend");
	}
	public function getCurrentAttributeSet() {
		return Mage::app()->getRequest()->getParam("attribute_set");
	}
}