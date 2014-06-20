<?php
class Zolago_Po_Block_Vendor_Po_Edit_Address 
	extends Zolago_Po_Block_Vendor_Po_Edit_Abstract
	implements Zolago_Po_Block_Vendor_Po_Edit_Address_Interface
{

	protected $_shippingTypes = array(
		Mage_Sales_Model_Order_Address::TYPE_SHIPPING, 
		Zolago_Po_Model_Po::TYPE_POSHIPPING
	);
	
	public function getFormUrl() {
		return $this->getPoUrl("saveAddress", array("type"=>$this->getType()));
	}
	
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
		return $this->getType()==Mage_Sales_Model_Order_Address::TYPE_SHIPPING;
	}
	
	public function getType() {
		if(in_array($this->getAddress()->getAddressType(), $this->_shippingTypes)){
			return Mage_Sales_Model_Order_Address::TYPE_SHIPPING;
		}else{
			return Mage_Sales_Model_Order_Address::TYPE_BILLING;
		}
	}
}
