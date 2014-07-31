<?php

class Zolago_Catalog_Block_Vendor_Price extends Mage_Core_Block_Template
{
	
	
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
	
	/**
	 * @return Mage_Catalog_Model_Resource_Product_Collection
	 */
	public function getCollection() {
		if(!$this->getData("collection")){
			$collection = Mage::getResourceModel("catalog/product_collection");
			/* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
			$collection->addAttributeToFilter("udropship_vendor", $this->getVendor()->getId());
			$collection->addAttributeToFilter("type_id", "configurable");
			$collection->setPageSize(1000);
			$this->setData("collection", $collection);
		}
		return $this->getData("collection");
	}
	
	public function getJsonCollection() {
		$out = array();
		foreach($this->getCollection() as $product){
			$product->setCollapsed(false);
			$out[] = $product->getData();
		}
		return Mage::helper("core")->jsonEncode($out);
	}
	

}