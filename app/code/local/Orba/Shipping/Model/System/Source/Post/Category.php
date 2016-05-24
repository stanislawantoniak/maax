<?php
class Orba_Shipping_Model_System_Source_Post_Category {

    const ECONOMIC = 'EKONOMICZNA';
    const PRIORITY = 'PRIORYTETOWA';
        	        

	protected $_hashes = array(
	    self::ECONOMIC => 'Economic',
	    self::PRIORITY => 'Priority',
	);

	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label);
		}
		return $out;
	}


}