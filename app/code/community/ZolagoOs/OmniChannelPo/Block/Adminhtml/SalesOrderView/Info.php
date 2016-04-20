<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_SalesOrderView_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    public function getCustomerViewUrl()
    {
        if ($this->getOrder()->getCustomerIsGuest()) {
            return false;
        }
        return $this->getUrl('adminhtml/customer/edit', array('id' => $this->getOrder()->getCustomerId()));
    }

    public function getViewUrl($orderId)
    {
        return $this->getUrl('adminhtml/sales_order/view', array('order_id'=>$orderId));
    }
}