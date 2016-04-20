<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_View_Comments extends Mage_Adminhtml_Block_Text_List
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