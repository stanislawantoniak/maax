<?php

class Zolago_Catalog_Block_Vendor_Mass extends Mage_Core_Block_Template
{
	public function _prepareLayout() {
		$this->_prepareGrid();
		$this->_prepareStoreSwitcher();
		parent::_prepareLayout();
	}
	
    public function _prepareGrid() {
		if($this->getCurrentAttributeSetId()){
			$design = Mage::getDesign();
			$design->setArea("adminhtml");
			$block = $this->getLayout()->
					createBlock("zolagocatalog/vendor_mass_grid");
			$block->setParentBlock($this);
			$this->setGridHtml($block->toHtml());
			$this->setGrid($block);
			$design->setArea("frontend");
		}
	}
	
    public function _prepareStoreSwitcher() {
		$design = Mage::getDesign();
		$design->setArea("adminhtml");
		$block = $this->getLayout()->
				createBlock("adminhtml/store_switcher");
		$block->setWebsiteIds($this->getPossibleWebsiteIds());
		$block->setUseConfirm(0);
		$block->setParentBlock($this);
		$this->setStoreSwitcherHtml($block->toHtml());
		$this->setStoreSwitcher($block);
		$design->setArea("frontend");
	}
	
	/**
	 * @return null | array
	 */
	public function getPossibleWebsiteIds() {
		$array = $this->getVendor()->getLimitWebsites();
		if(!is_array($array) || (count($array)==1 && $array[0]=="")){
			return null;
		}
		return $array;
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getCurrentAttributeSet() {
		if(!$this->getData("current_attribute_set")){
			$this->setData("current_attribute_set", Mage::getModel("eav/entity_attribute_set")->load(
				Mage::app()->getRequest()->getParam("attribute_set")
			));
		}
		return $this->getData("current_attribute_set");
	}
	
	/**
	 * @return int
	 */
	public function getCurrentAttributeSetId() {
		return $this->getCurrentAttributeSet()->getId();
	}
	
	/**
	 * @return Mage_Core_Model_Store
	 */
	public function getCurrentStore() {
		if(!$this->getData("current_store")){
			$store = Mage::getModel("core/store")->load(
					Mage::app()->getRequest()->getParam("store", 0)
			);
			if(!$store->getId()){
				$store = Mage::app()->getStore(Mage_Catalog_Model_Product::DEFAULT_STORE_ID);
			}
			$this->setData("current_store", $store);
		}
		return $this->getData("current_store");
	}
	
	/**
	 * @return int
	 */
	public function getCurrentStoreId() {
		return $this->getCurrentStore()->getId();
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Session
	 */
	protected function _getSession() {
		return Mage::getSingleton('udropship/session');
	}
	

}