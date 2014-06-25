<?php
class Zolago_Dropship_Model_Resource_Vendor extends Unirgy_Dropship_Model_Mysql4_Vendor
{
	
	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return array
	 */
	public function getChildVendorIds(Mage_Core_Model_Abstract $object) {
		$select = $this->getReadConnection()->select();
		$select->from(array("vendor"=>$this->getMainTable()), array("vendor_id"));
		$select->where("vendor.super_vendor_id=?", $object->getId());
		return $this->getReadConnection()->fetchCol($select);
	}
	
	
   	/**
	 * @param Mage_Core_Model_Abstract $object
	 * @return array
	 */
	public function getAllowedPos(Mage_Core_Model_Abstract $object) {
		if(!$object->getId()){
			return array();
		}
		$select = $this->getReadConnection()->select();
		$select->from(array("pos_vendor"=>$this->getTable("zolagopos/pos_vendor")), array("pos_id"));
		$select->where("pos_vendor.vendor_id=?", $object->getId());
		return $this->getReadConnection()->fetchCol($select);
	}
}
