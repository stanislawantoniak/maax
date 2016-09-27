<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Model_Form_Element_Links extends Varien_Data_Form_Element_Text
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
        $value = $this->getValue();
        $links = array();
        foreach (explode(",", trim($value)) as $link){
            $link = trim($link);
            if ($link){
                $links[] = $link;
            }
        }

        $html = "";
        if (count($links)){
            foreach ($links as $link){
                $html .= "<a href=\"{$link}\" target=\"_blank\">{$link}</a><br/>";
            }
        } else {
            $html .= $this->_getCommonHelper()->__('No links');
        }
        return $html;
    }
}