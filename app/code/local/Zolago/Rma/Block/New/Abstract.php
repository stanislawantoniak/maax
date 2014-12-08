<?php
abstract class Zolago_Rma_Block_New_Abstract extends  Zolago_Rma_Block_New{
	/**
	 * @param mixed $data
	 * @return string
	 */
	public function asJson($data) {
		return Mage::helper('core')->jsonEncode($data);
	}
	
	/**
	 * @return Mage_Customer_Model_Customer
	 */
	public function getCustomer() {
		return Mage::getSingleton('customer/session')->getCustomer();
	}
	
	/**
	 * @return string
	 */
	public function getFormKey() {
		return Mage::getSingleton('core/session')->getFormKey();
	}
	
}