<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

/** Filterable Container */
class Magpleasure_Blog_Block_Adminhtml_Filterable extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function isSingleStoreMode()
    {
        return $this->_helper()->getStoreHelper()->isSingleStoreMode();
    }

    public function isStoreFilterApplied()
    {
        return $this->_helper()->getStoreHelper()->isStoreFilterApplied();
    }

    public function getAppliedStoreId()
    {
        return $this->_helper()->getStoreHelper()->getAppliedStoreId();
    }

    protected function _getCommonParams()
    {
        return $this->_helper()->getStoreHelper()->getCommonParams();
    }

    public function getCreateUrl()
    {
        $params = $this->_getCommonParams();
        return $this->getUrl('*/*/new', $params);
    }
}