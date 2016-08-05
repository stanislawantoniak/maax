<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Filterable_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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

    /**
     * Retrive params with common filter flags
     *
     * @return array
     */
    protected function _getCommonParams()
    {
        return $this->_helper()->getStoreHelper()->getCommonParams();
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        if ($this->hasFormActionUrl()) {
            return $this->getData('form_action_url');
        }

        $params = $this->_getCommonParams();
        return $this->getUrl('*/' . $this->_controller . '/save', $params);
    }

    public function getBackUrl()
    {
        $params = $this->_getCommonParams();
        return $this->getUrl('*/*/', $params);
    }

    public function getDeleteUrl()
    {
        $params = $this->_getCommonParams();
        $params[$this->_objectId] = $this->getRequest()->getParam($this->_objectId);
        return $this->getUrl('*/*/delete', $params);
    }
}