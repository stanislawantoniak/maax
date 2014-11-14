<?php
/**
 * @method Mage_Sales_Model_Resource_Order_Collection getOrders() 
 */
class Zolago_Modago_Block_Sales_Order_History extends Zolago_Modago_Block_Sales_Order_Abstract
{
    protected $_opened_orders;
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/history.phtml');
    	$states = Mage::getSingleton('sales/order_config')->getHistoryStates();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('in' => $states))
            ->setOrder('created_at', 'desc');

        $this->setOrders($orders);
    }
    
    //{{{ 
    /**
     * count of opened orders
     * @return int
     */

    //}}}
    public function getOpenedOrders() {
        if ($this->_opened_orders === null) {
            $openedOrders = Mage::helper('zolagosales')->getOpenedOrders();
            $this->_opened_orders = count($openedOrders);
        }
        return $this->_opened_orders;
    }
    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', array('order_id' => $order->getId()));
    }

    public function getTrackUrl($order)
    {
        return $this->getUrl('*/*/track', array('order_id' => $order->getId()));
    }

    public function getReorderUrl($order)
    {
        return $this->getUrl('*/*/reorder', array('order_id' => $order->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
