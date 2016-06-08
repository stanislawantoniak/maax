<?php
class Orba_Shipping_Model_System_Source_Packstation_Sizes {
	/* VENDOR'S INPOST PARCEL SIZES */
	/* sizes in cm splitted during packages making */
	const INPOST_PARCEL_SIZE_SMALL = 'A';
	const INPOST_PARCEL_SIZE_MEDIUM = 'B';
	const INPOST_PARCEL_SIZE_BIG = 'C';

	protected $_hashes = array(
		self::INPOST_PARCEL_SIZE_SMALL => "Small parcel (8cm x 38cm x 64cm)",
		self::INPOST_PARCEL_SIZE_MEDIUM => "Medium parcel (19cm x 38cm x 64cm)",
		self::INPOST_PARCEL_SIZE_BIG => "Big parcel (41cm x 38cm x 64cm)",
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label);
		}
		return $out;
	}


}