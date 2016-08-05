<?php

class Snowdog_Freshmail_Block_System_Config_Form_Popup_Settings
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
            ->createBlock('snowfreshmail/system_config_form_popup_settings')
            ->setTemplate('snowfreshmail/system/config/form/popup/settings.phtml')
            ->toHtml();
        return $html;
    }

    /**
     * Get configuration value from Freshmail settings
     * 
     * @param string $field
     *
     * @return string
     */
    public function getValue($field)
    {
        return Mage::getStoreConfig('snowfreshmail/popup_settings/' . $field);
    }

    /**
     * Get CMS pages for select
     */
    public function getCmsPages()
    {
        return Mage::getModel('cms/page')->getCollection();
    }
}
