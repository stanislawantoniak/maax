<?php
class Zolago_Operator_Block_Dropship_Operator extends Mage_Core_Block_Template {
	
	/**
	 * @return Zolago_Operator_Model_Resource_Operator_Collection
	 */
	public function getCollection() {
		$vendor = $this->_getSession()->getVendor();
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagooperator/operator_collection");
		$collection->addVendorFilter($vendor);
		return $collection;
	}
	
	/**
	 * @return Unirgy_Dropship_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('udropship/session');
	}
}
