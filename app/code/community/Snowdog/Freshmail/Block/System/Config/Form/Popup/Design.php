<?php

class Snowdog_Freshmail_Block_System_Config_Form_Popup_Design
    extends Mage_Adminhtml_Block_Abstract
    implements Varien_Data_Form_Element_Renderer_Interface
{
    /**
     * Render Information element
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $this->getLayout()
            ->createBlock('snowfreshmail/system_config_form_popup_design')
            ->setTemplate('snowfreshmail/system/config/form/popup/design.phtml')
            ->toHtml();
        return $html;
    }

    /**
     * Render Popup Preview
     *
     * @return string
     */
    public function getPreview()
    {
        $html = $this->getLayout()
            ->createBlock('core/template')
            ->setTemplate('snowfreshmail/system/config/form/popup/preview.phtml')
            ->toHtml();
        return $html;
    }

    /**
     * Get configuration value from Freshmail design
     * 
     * @param string $field
     *
     * @return string
     */
    public function getValue($field)
    {
        return Mage::helper('snowfreshmail')->getPopupProperty($field);
    }

    /**
     * @param string        $field
     * @param null|mixed    $expected
     *
     * @return bool|mixed
     */
    public function configHas($field, $expected = null)
    {
        $value = $this->getValue($field);
        if (null === $expected) {
            return $value;
        }
        return $expected == $value;
    }
}
