<?php

class Zolago_Customer_Block_Privacy extends Mage_Core_Block_Template
{
	/**
	 * @return int (1/0)
	 */
	public function isForgetMeChecked() {
		return (int)$this->getSession()->getCustomer()->getForgetMe();
	}
	
	/**
	 * @return Mage_Customer_Model_Session
	 */
	public function getSession() {
		return Mage::getSingleton('customer/session');
	}
} 