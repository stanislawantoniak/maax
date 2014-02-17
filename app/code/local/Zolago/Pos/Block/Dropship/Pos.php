<?php
class Zolago_Pos_Block_Dropship_Pos extends Mage_Core_Block_Template {
	
	/**
	 * @return Zolago_Pos_Model_Resource_Pos_Collection
	 */
	public function getCollection() {
		$vendor = $this->_getSession()->getVendor();
		/* @var $vendor Unirgy_Dropship_Model_Vendor */
		$collection = Mage::getResourceModel("zolagopos/pos_collection");
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
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

?>
