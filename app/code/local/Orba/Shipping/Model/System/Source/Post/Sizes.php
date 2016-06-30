<?php
class Orba_Shipping_Model_System_Source_Post_Sizes {
	/* sizes in cm splitted during packages making */
	const GABARYT_A = 'GABARYT_A';
    const GABARYT_B = 'GABARYT_B';
	const XS = 'XS';
	const S = 'S';
	const M = 'M';
	const L = 'L';
	const XL = 'XL';
	const XXL = 'XXL';
	        

	protected $_standardHashes = array(
    	    self::GABARYT_A => 'A',
	        self::GABARYT_B => 'B',
	);

	protected $_businessHashes = array(
	    self::XS => 'XS',
	    self::S => 'S',
	    self::M => 'M',
	    self::L => 'L',
	    self::XL => 'XL',
	    self::XXL => 'XXL',
	);


	public function toOptionHash() {
		$out = array();
		if (Orba_Shipping_Model_Post_Client::useBusinessPackType()) {
		    $_hashes = $this->_businessHashes;
		} else {
		    $_hashes = $this->_standardHashes;
		}
		foreach($_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__('Gauge %s',$label);
		}
		return $out;
	}


}