<?php
class Zolago_Catalog_Model_System_Layer_Filter_Source {
	
	const TEST_ONE = "zolagocatalog/layer_filter_testone";
	const TEST_TWO = "zolagocatalog/layer_filter_testtwo";
	
	protected $_options = array(
		self::TEST_ONE => "Test one",
		self::TEST_TWO => "Test two",
	);

	public function toOptionHash($withEmpty=true) {
		$out = array();
		
		if($withEmpty){
			 $out['']='';
		}
		
		foreach($this->_options as $k=>$option){
			$out[$k]=Mage::helper("zolagocatalog")->__($option);
		}
		
		return $out;
	}
}