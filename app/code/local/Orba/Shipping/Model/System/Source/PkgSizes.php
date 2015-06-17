<?php
class Orba_Shipping_Model_System_Source_PkgSizes {
	/* VENDOR'S DHL PARCEL SIZES */
	/* sizes in cm splitted during packages making */
	const DHL_PARCEL_SIZE_SMALL = '10x20x40';
	const DHL_PARCEL_SIZE_MEDIUM = '20x40x60';
	const DHL_PARCEL_SIZE_BIG = '40x50x60';

	protected $_hashes = array(
		self::DHL_PARCEL_SIZE_SMALL => "Small parcel",
		self::DHL_PARCEL_SIZE_MEDIUM => "Medium parcel",
		self::DHL_PARCEL_SIZE_BIG => "Big parcel"
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label)." ".$value."cm";
		}
		return $out;
	}


}