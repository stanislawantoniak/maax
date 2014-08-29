<?php

abstract class Zolago_Catalog_Block_Vendor_Price_Abstract extends Mage_Core_Block_Template
{
	/**
	 * @return array
	 */
	public function getPriceSourceOptions() {
		$priceType = Mage::getSingleton('eav/config')->getAttribute(
			Mage_Catalog_Model_Product::ENTITY,
			Zolago_Catalog_Model_Product::ZOLAGO_CATALOG_CONVERTER_MSRP_TYPE_CODE
		);
		$priceType->setStoreId($this->getCurrentStoreId());

		return $priceType->getSource()->getAllOptions();
	}
	
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
	
	/**
	 * @return int
	 */
	public function getCurrentStoreId() {
		$store = Mage::app()->getStore(Mage::app()->getRequest()->getParam("store_id"));
		return ($store && $store->getId()) ? $store->getId() : Mage::app()->getStore()->getId();
	}
	

}