<?php
class Zolago_Po_Block_Vendor_Po_Edit_Address 
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
{

	public function isSameAsOrigin($type) {
		if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
			return $this->getPo()->isShippingSameAsOrder();
		}elseif($type==Mage_Sales_Model_Order_Address::TYPE_BILLING){
			return $this->getPo()->isBillingSameAsOrder();
		}
		return true;
	}
	
	public function isBilling() {
		return $this->getType()==Mage_Sales_Model_Order_Address::TYPE_BILLING;
	}
	
	public function isShipping() {
		return $this->getType()==Mage_Sales_Model_Order_Address::TYPE_BILLING;
	}
	
	public function getType() {
		if(in_array($this->getAddress()->getAddressType(), array("shipping", "poshipping"))){
			return Mage_Sales_Model_Order_Address::TYPE_SHIPPING;
		}else{
			return Mage_Sales_Model_Order_Address::TYPE_BILLING;
		}
	}
}
