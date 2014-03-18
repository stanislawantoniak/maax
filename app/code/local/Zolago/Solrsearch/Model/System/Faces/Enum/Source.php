<?php
class Zolago_Solrsearch_Model_System_Faces_Enum_Source {
	
	const TEST_ONE = "zolagosolrsearch/faces_enum_blue";
	const TEST_TWO = "zolagosolrsearch/faces_enum_yellow";
	
	protected $_options = array(
		self::TEST_ONE => "Blue",
		self::TEST_TWO => "Yellow",
	);

	public function toOptionHash($withEmpty=true) {
		$out = array();
		
		if($withEmpty){
			 $out['']='--- Select ---';
		}
		
		foreach($this->_options as $k=>$option){
			$out[$k]=Mage::helper("zolagosolrsearch")->__($option);
		}
		
		return $out;
	}
}