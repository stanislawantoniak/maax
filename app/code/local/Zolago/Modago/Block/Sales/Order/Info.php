<?php
/**
 * shipping address and billing type
 */
class Zolago_Modago_Block_Sales_Order_Info extends Mage_Core_Block_Template {
    protected function _construct() {
        $this->setTemplate('sales/order/info.phtml');
        parent::_construct();
    }
}