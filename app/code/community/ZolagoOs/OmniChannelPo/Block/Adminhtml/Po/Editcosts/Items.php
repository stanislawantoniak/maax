<?php
/**
  
 */
 
class ZolagoOs_OmniChannelPo_Block_Adminhtml_Po_Editcosts_Items extends Mage_Adminhtml_Block_Sales_Items_Abstract
{
    public function getPo()
    {
        return Mage::registry('current_udpo');
    }
    
    public function getOrder()
    {
        return Mage::registry('current_order');
    }
    
    protected function _beforeToHtml()
    {
        $this->setChild(
            'submit_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
                'label'     => Mage::helper('udpo')->__('Save Costs Update'),
                'class'     => 'save submit-button',
                'onclick'   => 'disableElements(\'submit-button\');$(\'edit_form\').submit()',
            ))
        );

        return parent::_beforeToHtml();
    }
    
    public function getCommentText()
    {
        return Mage::getSingleton('adminhtml/session')->getCommentText(true);
    }
}