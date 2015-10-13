<?php

/**
 * fieldset renderer
 */
class Zolago_Dropship_Block_Form_Renderer_Actionbutton
    extends Varien_Data_Form_Element_Abstract
{
    protected $_element;

    public function getElementHtml()
    {
        $hlp = Mage::helper('udropship');
        $classDisabled = $this->getDisabled() ? "disabled" : "";
        $html = '<button class="scalable '.$classDisabled.' save" disabled="'.$this->getDisabled().'" name="'.$this->getName().'" type="button" title="'.$hlp->__('Send').'" ><span>'.$hlp->__('Send').'</span></button>';
        return $html;
    }
}