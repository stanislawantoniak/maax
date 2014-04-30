<?php
class Zolago_Po_Block_Vendor_Po_Edit_Shipping 
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{

	const MODE_GENERATE		= "generate";
	const MODE_ADD			= "add";
	
	public function getMode() {
		return $this->canPosUseDhl() ? self::MODE_GENERATE : self::MODE_ADD;
	}
	
	public function getAvailableMethods(){
		return $this->getParentBlock()->getAvailableMethods();
	}
	
	public function isMethodChecked($code){
		return $code=="zolagodhl";
	}
	public function getCarriers(){
		return $this->getParentBlock()->getCarriers();
	}


	public function canUseCarrier() {
		return $this->getParentBlock()->canUseCarrier();
	}
	
	public function canPosUseDhl() {
		return $this->getParentBlock()->canPosUseDhl();
	}
}
