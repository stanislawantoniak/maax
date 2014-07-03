<?php
class Zolago_Po_Block_Vendor_Po_Edit_Shipping 
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
	implements Zolago_Po_Block_Vendor_Po_Edit_Shipping_Interface
{

	public function isMethodChecked($code){
		return $code == Zolago_Dhl_Model_Carrier::CODE;
	}
	
	public function getFormUrl() {
		return  $this->getPoUrl("saveShipping", array("mode"=>$this->getMode()));
	}
	
	public function getMode() {
		return $this->canPosUseDhl() ? self::MODE_GENERATE : self::MODE_ADD;
	}
	
	public function getAvailableMethods(){
		return $this->getParentBlock()->getAvailableMethods();
	}
	
	public function getRemainingShippingAmount() {
		return $this->getParentBlock()->getRemainingShippingAmount();
	}
	public function getRemainingWeight() {
		return $this->getParentBlock()->getRemainingWeight();
	}
	
	public function getShippingMethod() {
		return $this->getParentBlock()->getShippingMethod();
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
