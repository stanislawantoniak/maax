<?php
class Orba_Shipping_Model_System_Source_PkgSizes {
	/* VENDOR'S DHL PARCEL SIZES */
	/* sizes in cm splitted during packages making */
	const DHL_PARCEL_SIZE_SMALL = '30x40x45';
	const DHL_PARCEL_SIZE_MEDIUM = '40x50x60';
	const DHL_PARCEL_SIZE_BIG = '50x60x75';

	protected $_hashes = array(
		self::DHL_PARCEL_SIZE_SMALL => "Small parcel (biggest dimension is less than 45cm)",
		self::DHL_PARCEL_SIZE_MEDIUM => "Medium parcel (biggest dimension is less than 60cm)",
		self::DHL_PARCEL_SIZE_BIG => "Big parcel (biggest dimension is less than 75cm)"
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label)." ".$value."cm";
		}
		return $out;
	}


}