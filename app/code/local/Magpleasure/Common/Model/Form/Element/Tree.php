<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Form_Element_Tree extends Varien_Data_Form_Element_Text
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _getCommonHelper()
    {
        return Mage::helper('magpleasure');
    }

    /**
     * Retrives element html
     * @return string
     */
    public function getElementHtml()
    {
        $category = new Magpleasure_Common_Block_System_Entity_Form_Element_Tree_Render($this->getData());
        $category
            ->addData($this->getData())
            ->setLayout(Mage::app()->getLayout())
            ;

        $html = '';
        $html .= $category->toHtml();

        $html.= $this->getAfterElementHtml();
        return $html;
    }
}