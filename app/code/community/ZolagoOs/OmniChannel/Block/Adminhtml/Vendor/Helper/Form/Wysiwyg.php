<?php
/**
  
 */

class ZolagoOs_OmniChannel_Block_Adminhtml_Vendor_Helper_Form_Wysiwyg extends Varien_Data_Form_Element_Textarea
{
    public function getAfterElementHtml()
    {
        $html = parent::getAfterElementHtml();
        if ($this->isWysiwygAllowed()) {
            $html .= Mage::getSingleton('core/layout')
                ->createBlock('adminhtml/widget_button', '', array(
                    'label'   => Mage::helper('catalog')->__('WYSIWYG Editor'),
                    'type'    => 'button',
                    'disabled' => false,
                    'class' => '',
                    'onclick' => 'uVendorWysiwygEditor.open(\''.Mage::helper('adminhtml')->getUrl('*/*/wysiwyg').'\', \''.$this->getHtmlId().'\')'
                ))->toHtml();
        }
        return $html;
    }

    public function isWysiwygAllowed()
    {
        return Mage::helper('udropship')->isWysiwygAllowed();
    }
}

