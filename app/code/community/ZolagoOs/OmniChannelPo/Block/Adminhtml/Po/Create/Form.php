<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Create_Form extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getPaymentHtml()
    {
        return $this->getChildHtml('order_payment');
    }

    public function getItemsHtml()
    {
        return $this->getChildHtml('order_items');
    }

    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', array('order_id' => $this->getOrder()->getId()));
    }
}