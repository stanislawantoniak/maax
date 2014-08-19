<?php

abstract class Zolago_Catalog_Block_Vendor_Price_Abstract extends Mage_Core_Block_Template
{
	
	/**
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct() {
		return Mage::registry("current_product");
	}
	
	/**
	 * @return Zolago_Dropship_Model_Vendor
	 */
	protected function _getVendor() {
		return Mage::getSingleton('udropship/session')->getVendor();
	}
	

}