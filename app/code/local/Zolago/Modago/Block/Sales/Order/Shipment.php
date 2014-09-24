<?php
/**
 * shipment info
 */
class Zolago_Modago_Block_Sales_Order_Shipment extends Mage_Core_Block_Template {
    protected function _construct() {
        $this->setTemplate('sales/order/shipment.phtml');
        parent::_construct();
    }
}