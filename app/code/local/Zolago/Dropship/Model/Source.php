<?php

class Zolago_Dropship_Model_Source extends Unirgy_Dropship_Model_Source
{
	public function toOptionHash($selector=false){
		if($this->getPath()=="allvendorswithempty"){
			$out = $this->getVendors();
			$out = array_reverse($out, true);
			$out[""] = "";
			return array_reverse($out, true);;
		}
	   return parent::toOptionHash($selector);;
   }
}
 