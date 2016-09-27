<?php
class Zolago_Solrsearch_Model_System_Faces_Enum_Source {
	
	const SIZE		= "zolagosolrsearch/faces_enum_size";
	const COLOR		= "zolagosolrsearch/faces_enum_color";
	const LONGLIST	= "zolagosolrsearch/faces_enum_longlist";
	const DROPLIST	= "zolagosolrsearch/faces_enum_droplist";
	const ICON		= "zolagosolrsearch/faces_enum_icon";
	
	protected $_options = array(
		//self::SIZE		=> "Size",
		//self::COLOR		=> "Color",
		self::LONGLIST	=> "Long list",
		self::DROPLIST	=> "Dropdown",
		//self::ICON		=> "Icons",
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