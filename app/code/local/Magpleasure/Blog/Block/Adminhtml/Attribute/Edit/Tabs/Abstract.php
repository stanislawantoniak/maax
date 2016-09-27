<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

abstract class Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Abstract
    extends Magpleasure_Blog_Block_Adminhtml_Filterable_Widget_Form
{
    protected $_values = array();

    protected function _isChecked($key)
    {
        $values = $this->_getValues();
        return isset($values[$key]);
    }

    protected function _getValues()
    {
        if (Mage::getSingleton('adminhtml/session')->getAttributeUpdateData()) {
            $this->_values = Mage::getSingleton('adminhtml/session')->getAttributeUpdateData();
        }

        return $this->_values;
    }
}
