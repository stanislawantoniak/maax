<?php
class Orba_Shipping_Model_System_Source_Pkg_Sizes {
	/* VENDOR'S DHL PARCEL SIZES */
	/* sizes in cm splitted during packages making */
	const DHL_PARCEL_SIZE_SMALL = '20x30x15';
	const DHL_PARCEL_SIZE_MEDIUM = '30x40x25';
	const DHL_PARCEL_SIZE_BIG = '40x50x35';
	const DHL_PARCEL_SIZE_LARGE = '45x60x40';

	protected $_hashes = array(
		self::DHL_PARCEL_SIZE_SMALL => "Small parcel (biggest dimension is less than 30cm)",
		self::DHL_PARCEL_SIZE_MEDIUM => "Medium parcel (biggest dimension is less than 40cm)",
		self::DHL_PARCEL_SIZE_BIG => "Big parcel (biggest dimension is less than 50cm)",
		self::DHL_PARCEL_SIZE_LARGE => "Large parcel (biggest dimension is less than 60cm)"
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label)." ".$value."cm";
		}
		return $out;
	}


}