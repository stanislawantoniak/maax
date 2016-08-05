<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_File_Upload extends Varien_Data_Form_Element_Imagefile
{
    public function getRenderer()
    {
        $control = new Magpleasure_Common_Block_System_Entity_Form_Element_File_Upload_Render($this->getData());
        $control->setLayout(Mage::app()->getLayout());

        if (Mage::registry('current_product')){
            $control->setData('name', 'product['.$control->getName().']');
        }

        return $control;
    }


    public function getElementHtml()
    {
        $html = '';
        $html .= $this->getRenderer()->toHtml();
        $html .= $this->getAfterElementHtml();
        return $html;
    }
}