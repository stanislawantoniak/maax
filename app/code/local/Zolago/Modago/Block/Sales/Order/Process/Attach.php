<?php
class Zolago_Modago_Block_Sales_Order_Process_Attach extends Mage_Core_Block_Template {

	protected function _construct() {
		parent::_construct();
		$this->setTemplate('sales/order/process/attach.phtml');
	}
}