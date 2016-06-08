<?php
class Orba_Shipping_Model_System_Source_Pkg_Type {
	const TYPE_PACKAGE		= "PACKAGE";
	const TYPE_ENVELOPE	= "ENVELOPE";
	const TYPE_PALLET		= "PALLET";
	
	protected $_hashes = array(
		self::TYPE_PACKAGE => "Package",
		self::TYPE_ENVELOPE => "Envelope",
		/*self::TYPE_PALLET => "Pallet"*/
	);
	
	public function toOptionHash() {
		$out = array();
		foreach($this->_hashes as $value=>$label){
			$out[$value] = Mage::helper('orbashipping')->__($label);
		}
		return $out;
	}
}