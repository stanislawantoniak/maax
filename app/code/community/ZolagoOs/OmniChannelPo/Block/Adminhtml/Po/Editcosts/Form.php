<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Editcosts_Form extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    public function getPo()
    {
        return Mage::registry('current_udpo');
    }
    
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
        return $this->getUrl('*/*/saveCosts', array('udpo_id' => $this->getPo()->getId()));
    }
}
