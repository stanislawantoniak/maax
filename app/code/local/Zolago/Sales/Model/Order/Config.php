<?php
/**
 * extends list of statuses
 */
class Zolago_Sales_Model_Order_Config extends Mage_Sales_Model_Order_Config {
    public function getHistoryStates() {        
        return array(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_COMPLETE,
        );
    }
    public function getOpenOrdersStates() {
        return array(
            Mage_Sales_Model_Order::STATE_NEW,
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage_Sales_Model_Order::STATE_HOLDED,
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW,            
        );
    }
}