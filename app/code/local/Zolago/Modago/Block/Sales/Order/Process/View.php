<?php

class Zolago_Modago_Block_Sales_Order_Process_View 
	extends Zolago_Modago_Block_Sales_Order_View {
	
	/**
	 * @return Zolago_Modago_Block_Sales_Order_View
	 */
	protected function _prepareLayout() {
		return $this;
	}
	
	/**
	 * @return Mage_Sales_Model_Order
	 */
	public function getOrder() {
		return $this->getData("order");
	}
	
	/**
	 * @return bool
	 */
	public function getHasAnyPo() {
		return (bool)$this->getItems()->count();
	}
}
