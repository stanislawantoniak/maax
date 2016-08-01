<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Abstract Collection
     *
     * @return Magpleasure_Common_Model_Resource_Collection_Abstract
     */
    public function getCollection()
    {
        return parent::getCollection();
    }

    protected function _filterCommonProductName($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addCommonProductName($value);
    }

    protected function _filterCommonSalesOrderId($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addCommonSalesOrderId($value);
    }

    protected function _filterCommonCmsBlock($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addCommonCmsBlockId($value);
    }

    protected function _filterCommonCustomerId($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $this->getCollection()->addCommonCustomerId($value);
    }

    protected function _getBeforeGridHtml(){}

    protected function _getAfterGridHtml(){}

    protected function _toHtml()
    {
        $html = parent::_toHtml();
        if ($this->getRequest()->getParam('isAjax')){
            return $html;
        }
        return $this->_getBeforeGridHtml().$html.$this->_getAfterGridHtml();
    }
}

