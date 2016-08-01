<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_Tree extends Varien_Data_Form_Element_Text
{

    public function getElementHtml()
    {
        $category = new Magpleasure_Common_Block_System_Entity_Form_Element_Tree_Render($this->getData());
        $category->setLayout(Mage::app()->getLayout());

        if (Mage::registry('current_product')){            
            $category->setData('name', 'product['.$category->getName().']');
        }

        $html = '';
        $html .= $category->toHtml();

        $html.= $this->getAfterElementHtml();
        return $html;
    }
}