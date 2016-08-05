<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Filterable_Widget_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getAppliedStoreId()
    {
        return $this->_helper()->getStoreHelper()->getAppliedStoreId();
    }

    public function isStoreFilterApplied()
    {
        return $this->_helper()->getStoreHelper()->isStoreFilterApplied();
    }

    protected function _getCommonParams()
    {
        return $this->_helper()->getStoreHelper()->getCommonParams();
    }
}