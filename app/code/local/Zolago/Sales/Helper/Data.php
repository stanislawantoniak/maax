<?php
class Zolago_Sales_Helper_Data extends Mage_Sales_Helper_Data {

    public function getOpenedOrders() {	
        $openedOrders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => Mage::getSingleton('sales/order_config')->getOpenOrdersStates()));
        return $openedOrders;
    }
} 