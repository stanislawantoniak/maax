<?php
class Zolago_Po_Block_Vendor_Po_Edit_Pos
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{

	public function getPosCollection() {
		$collection = Mage::getResourceModel('zolagopos/pos_collection');
		/* @var $collection Zolago_Pos_Model_Resource_Pos_Collection */
		$collection->addVendorFilter($this->getVendor());
		return $collection;
	}
}
