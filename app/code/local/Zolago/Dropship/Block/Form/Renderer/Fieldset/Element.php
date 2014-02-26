<?php
/**
 * fieldset element renderer
 */
class Zolago_Dropship_Block_Form_Renderer_Fieldset_Element 
	extends Mage_Core_Block_Template 
	implements Varien_Data_Form_Element_Renderer_Interface{
	
    protected function _construct() {
        $this->setTemplate('zolagodropship/form/renderer/fieldset/element.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->_element = $element;
        return $this->toHtml();
    }

}