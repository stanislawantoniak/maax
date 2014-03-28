<?php

class Zolago_Catalog_Model_Resource_Vendor_Mass 
	extends Mage_Core_Model_Resource_Db_Abstract{
	
	protected function _construct() {
		$this->_init("udropship/vendor_product_assoc", null);
	}
	
	public function getAttributeSetsForVendor(Unirgy_Dropship_Model_Vendor $vendor) {
		
		$select = $this->getReadConnection()->select();
		
		$select->from(
				array("index"=>$this->getMainTable()), array()
		);
		$select->join(
				array("product"=>$this->getTable('catalog/product')),
				"product.entity_id=index.product_id",
				array()
		);
		$select->join(
				array("attribute_set"=>$this->getTable('eav/attribute_set')),
				"attribute_set.attribute_set_id=product.attribute_set_id",
				array("attribute_set_id","attribute_set_name")
		);
		
		$select->where("index.vendor_id=?", $vendor->getId());
		$select->distinct(true);
		$select->order("attribute_set_name");
		
		return  $this->getReadConnection()->fetchPairs($select);
	}
}