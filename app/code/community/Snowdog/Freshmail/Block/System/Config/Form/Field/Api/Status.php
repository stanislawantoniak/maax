<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_Api_Status
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Get api connection status info
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        if (Mage::helper('snowfreshmail/api')->isConnected()) {
            $element->addClass('bar-green');
            $element->setValue(Mage::helper('snowfreshmail')->__('Connected'));
        } else {
            $element->addClass('bar-red');
            $element->setValue(Mage::helper('snowfreshmail')->__('Not connected'));
        }

        $html = '<span class="' . $element->getClass() . '"><span>';
        $html .= $element->getValue();
        $html .= '</span></span>';
        return $html;
    }
}
