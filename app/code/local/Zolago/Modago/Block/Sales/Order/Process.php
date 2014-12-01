<?php
/**
 * @method Mage_Sales_Model_Resource_Order_Collection getOrders() 
 */
class Zolago_Modago_Block_Sales_Order_Process 
	extends Zolago_Modago_Block_Sales_Order_Abstract
{
	
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('sales/order/process.phtml');
    	$states = Mage::getSingleton('sales/order_config')->getHistoryStates();
        $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addFieldToFilter('state', array('nin' => $states))
            ->setOrder('created_at', 'desc');

        $this->setOrders($orders);
    }
	
	/**
	 * @return string
	 */
	public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
	
	/**
	 * @param Mage_Sales_Model_Order $order
	 * @return string
	 */
	public function getOrderHtml(Mage_Sales_Model_Order $order) {
		return $this->getLayout()->
			createBlock('zolagomodago/sales_order_process_view')->
			setOrder($order)->
			setTemplate("sales/order/process/view.phtml")->
			toHtml();
	}
    
}
