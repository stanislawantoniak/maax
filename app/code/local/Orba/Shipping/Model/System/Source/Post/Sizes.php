<?php
class Orba_Shipping_Model_System_Source_Post_Sizes {
	/* sizes in cm splitted during packages making */
	const GABARYT_A = 'GABARYT_A';
    const GABARYT_B = 'GABARYT_B';
	        

	protected $_hashes = array(
	    self::GABARYT_A => 'A',
	    self::GABARYT_B => 'B',
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__('Gauge %s',$label);
		}
		return $out;
	}


}