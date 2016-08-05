<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_System_Entity_Form_Element_Grid_Class extends Varien_Data_Form_Element_Abstract
{
    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getOptionsJson()
    {
        return Zend_Json::encode($this->_getOptionsData());
    }

    protected function _getOptionsData()
    {
        $options = array(
            'w1' => $this->_helper()->__("Normal"),
            'w2' => $this->_helper()->__("Middle"),
            'w3' => $this->_helper()->__("Wide"),
        );

        return $this->_helper()->getCommon()->getArrays()->paramsToValueLabel($options);
    }

    public function getElementHtml()
    {
        $value = $this->getEscapedValue();
        return "<div class='mp-selector' mp-name='{$this->getName()}' mp-value='{$value}' mp-options='{$this->_getOptionsJson()}'></div>";
    }



}




