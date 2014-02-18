<?php
/**
 * fieldset renderer
 */
class Zolago_Dropship_Block_Form_Renderer_Fieldset extends Mage_Core_Block_Template {
    protected function _construct() {
        $this->setTemplate('form/renderer/fieldset.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Mage_Core_Block_Template $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

}