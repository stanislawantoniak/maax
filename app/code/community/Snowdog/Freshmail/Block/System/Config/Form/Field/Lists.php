<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_Lists
    extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Add a refresh button after the lists select
     *
     * @param Varien_Data_Form_Element_Abstract $element
     *
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = $element->setStyle('width:200px')->getElementHtml();
        $html .= $this->getButtonHtml();
        return $html;
    }

    /**
     * Generate button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'id' => 'snowfreshmail_lists_refresh',
                'label' => $this->helper('snowfreshmail')->__('Refresh'),
                'onclick' => "setLocation('" . $this->_getRefreshUrl() . "')",
            ));
        return $button->toHtml();
    }

    /**
     * Retrieve a link to refresh lists
     *
     * @return string
     */
    protected function _getRefreshUrl()
    {
        return $this->getUrl('adminhtml/freshmail/refreshLists');
    }
}
