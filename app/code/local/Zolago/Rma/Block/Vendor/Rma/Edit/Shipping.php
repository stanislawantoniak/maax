<?php
class Zolago_Rma_Block_Vendor_Rma_Edit_Shipping 
	extends Zolago_Rma_Block_Vendor_Rma_Edit_Abstract
	implements Zolago_Po_Block_Vendor_Po_Edit_Shipping_Interface
{
	
	public function isMethodChecked($code){
		return $code == Orba_Shipping_Model_Carrier_Dhl::CODE;
	}
	
	public function getFormUrl() {
		return $this->getRmaUrl("saveShipping", array("mode"=>$this->getMode()));
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
