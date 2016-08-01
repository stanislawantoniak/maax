<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_System_Entity_Form_Element_Dropdown_Render extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Path to element template
     */
    const TEMPLATE_PATH = 'magpleasure/system/config/form/element/dropdown.phtml';

    protected function  _construct()
    {
        parent::_construct();
        $this->setTemplate(self::TEMPLATE_PATH);

    }

    public function getName()
    {
        return $this->getData('name') ? $this->getData('name') : $this->getData('html_id');
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

    public function isAjax()
    {
        return $this->_commonHelper()->getRequest()->isAjax();
    }

    public function getParamsJson()
    {
        $params = array();

        if ($this->getFormatSelection()){
            $params[] = sprintf("formatSelection : %s", $this->getFormatSelection());
        }

        if ($this->getFormatResult()){
            $params[] = sprintf("formatResult : %s", $this->getFormatResult());
        }

        if ($this->getCanUseDefaultValue() || $this->getCanUseWebsiteValue()){
            $params[] = "disabled : true";
        }

        return "{".implode(",", $params)."}";
    }
}
