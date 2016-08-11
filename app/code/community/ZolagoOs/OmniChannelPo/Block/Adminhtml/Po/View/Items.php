<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_View_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    public function getPo()
    {
        return Mage::registry('current_udpo');
    }

    public function getOrder()
    {
        return $this->getPo()->getOrder();
    }

    public function getSource()
    {
        return $this->getPo();
    }
}