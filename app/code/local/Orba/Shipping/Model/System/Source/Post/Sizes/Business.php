<?php
class Orba_Shipping_Model_System_Source_Post_Sizes_Business {
	/* sizes in cm splitted during packages making */
	const XS = 'XS';
	const S = 'S';
	const M = 'M';
	const L = 'L';
	const XL = 'XL';
	const XXL = 'XXL';
	        

	protected $_hashes = array(
	    self::XS => 'XS',
	    self::S => 'S',
	    self::M => 'M',
	    self::L => 'L',
	    self::XL => 'XL',
	    self::XXL => 'XXL',
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__('Gauge %s',$label);
		}
		return $out;
	}


}