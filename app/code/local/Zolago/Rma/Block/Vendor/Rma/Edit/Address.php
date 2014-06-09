<?php
class Zolago_Rma_Block_Vendor_Rma_Edit_Address 
	extends Zolago_Rma_Block_Vendor_Rma_Edit_Abstract
	implements Zolago_Po_Block_Vendor_Po_Edit_Address_Interface
{
	
	public function getFormUrl() {
		return $this->getRmaUrl("saveAddress", array("type"=>$this->getType()));
	}
	
	protected $_shippingTypes = array(
		Mage_Sales_Model_Order_Address::TYPE_SHIPPING, 
		Zolago_Po_Model_Po::TYPE_POSHIPPING, 
		Zolago_Rma_Model_Rma::TYPE_RMASHIPPING
	);
	
	public function isSameAsOrigin($type) {
		if($type==Mage_Sales_Model_Order_Address::TYPE_SHIPPING){
			return $this->getRma()->isShippingSameAsPo();
		}elseif($type==Mage_Sales_Model_Order_Address::TYPE_BILLING){
			return $this->getRma()->isShippingSameAsPo();
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
