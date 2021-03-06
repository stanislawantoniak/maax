<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Create_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
    
    protected function _beforeToHtml()
    {
        $onclick = "submitAndReloadArea($('po_items_container'),'".$this->getUpdateUrl()."')";
        $this->setChild(
            'update_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'class'     => 'update-button',
                'label'     => Mage::helper('sales')->__('Update Qty\'s'),
                'onclick'   => $onclick,
            ))
        );
        $this->setChild(
            'submit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('udpo')->__('Create Purchase Orders'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
            ))
        );

        return parent::_beforeToHtml();
    }
    
    public function getUpdateButtonHtml()
    {
        return $this->getChildHtml('update_button');
    }
    
    public function getUpdateUrl()
    {
        return $this->getUrl('*/*/updateQty', array('order_id'=>$this->getOrder()->getId()));
    }
    
    public function getCommentText()
    {
        return Mage::getSingleton('adminhtml/session')->getCommentText(true);
    }
}