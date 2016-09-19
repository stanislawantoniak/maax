<?php
abstract class Zolago_Po_Block_Vendor_Po_Edit_Abstract
	extends Mage_Core_Block_Template
{
	/**
	 * @return Zolago_Po_Model_Po
	 */
	public function getPo() {
		return $this->getParentBlock()->getPo();
	}
	
	public function getPoUrl($action, $params=array()) {
		return $this->getParentBlock()->getPoUrl($action, $params);
	}
	
	public function getVendor() {
		return $this->getParentBlock()->getVendor();
	}
	
	/**
	 * @param type $storeId
	 * @return Mage_Catalog_Model_Entity_Attribute
	 */
	public function getSkuAttribute($storeId=null) {
		return Mage::helper('udropship')->getVendorSkuAttribute($storeId);
	}
	
	public function getCarrierName() {
		$po = $this->getPo();
		$shipping = $po->getShippingMethodInfo();
		$title = $shipping->getStoreTitle(Mage::app()->getStore()->getId());
		return $title;
	}
}
