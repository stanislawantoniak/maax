<?php

class Zolago_Catalog_Block_Vendor_Product extends Mage_Core_Block_Template
{

	public function getCanShowGrid() {
		return (bool)$this->getAttributeSetId();
	}
	
	/**
	 * @return Zolago_Catalog_Model_Vendor_Product_Grid
	 */
	public function getGridModel() {
		return Mage::getSingleton('zolagocatalog/vendor_product_grid');
	}
	
	/**
	 * @return Mage_Eav_Model_Entity_Attribute_Set
	 */
	public function getAttributeSet() {
		return $this->getGridModel()->getAttributeSet();
	}
	
	/**
	 * @return int
	 */
	public function getAttributeSetId() {
		return $this->getAttributeSet()->getId();
	}
	
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Vendor
	 */
	public function getVendor() {
		return $this->_getSession()->getVendor();
	}
	
	/**
	 * @return ZolagoOs_OmniChannel_Model_Session
	 */
	protected function _getSession() {
		return Mage::getSingleton('udropship/session');
	}
	

}