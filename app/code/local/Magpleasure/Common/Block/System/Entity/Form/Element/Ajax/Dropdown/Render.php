<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_Ajax_Dropdown_Render extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Path to element template
     */
    const TEMPLATE_PATH = 'magpleasure/system/config/form/element/ajax/dropdown.phtml';

    const DEFAULT_LIMIT = 10;

    protected $_datasource;

    protected function  _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);
    }

    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    protected function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    public function getName()
    {
        return $this->getData('name') ? $this->getData('name') : $this->getData('html_id');
    }

    public function getUrlPattern()
    {
        $params = $this->_commonHelper()->getWidgets()->getAjaxDropdown()->getParamsPattern();
        $params['h'] = $this->_commonHelper()->getHash()->getHash($this->getData('data_source'));
        return $this->getUrl('adminhtml/magpleasure_ajaxdropdown/list', $params);
    }

    public function getEntityUrlPattern()
    {
        $params = $this->_commonHelper()->getWidgets()->getAjaxDropdown()->getParamsPattern();
        $params['h'] = $this->_commonHelper()->getHash()->getHash($this->getData('data_source'));
        return $this->getUrl('adminhtml/magpleasure_ajaxdropdown/entity', $params);
    }

    /**
     * Datasource
     *
     * @return Magpleasure_Common_Model_Datasource
     */
    public function getDatasource()
    {
        if (!$this->_datasource){
            /** @var $datasource Magpleasure_Common_Model_Datasource */
            $datasource = Mage::getModel('magpleasure/datasource');
            $datasource->setParams($this->getData('data_source'));
            $this->_datasource = $datasource;
        }
        return $this->_datasource;
    }

    public function getResolvedValue()
    {
        return $this->getDatasource()->getLabelByValue($this->getValue());
    }

    public function getLimit()
    {
        return $this->getData('limit') ? $this->getData('limit') : self::DEFAULT_LIMIT;
    }

    public function isAjax()
    {
        return $this->_commonHelper()->getRequest()->isAjax();
    }
}
