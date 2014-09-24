<?php

class Zolago_Modago_Block_Sales_Order_Abstract extends Mage_Core_Block_Template
{
    
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return type
	 */
	public function getVendors(Mage_Sales_Model_Order $order) {
		
		return array();
	}
    
}
