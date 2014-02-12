<?php

class Unirgy_DropshipTierShipping_Block_Adminhtml_SystemConfigField_V2_SimpleCondRates extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('udtiership/system/form_field/v2/simple_cond_rates.phtml');
        }
        if (($head = Mage::app()->getLayout()->getBlock('head'))) {
            $head->setCanLoadExtJs(true);
        }
    }

    public function getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_getElementHtml($element);
    }
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        if (!$this->getDeliveryType()) {
            $html = '<div id="'.$element->getHtmlId().'_container"></div>';
        } else {
            $html = $this->_toHtml();
        }
        return $html;
    }

    public function getFieldName()
    {
        return $this->getData('field_name')
            ? $this->getData('field_name')
            : ($this->getElement() ? $this->getElement()->getName() : '');
    }

    protected $_idSuffix;
    public function resetIdSuffix()
    {
        $this->_idSuffix = null;
        return $this;
    }
    public function getIdSuffix()
    {
        if (null === $this->_idSuffix) {
            $this->_idSuffix = $this->prepareIdSuffix($this->getFieldName());
        }
        return $this->_idSuffix;
    }

    public function prepareIdSuffix($id)
    {
        return preg_replace('/[^a-zA-Z0-9\$]/', '_', $id);
    }

    public function suffixId($id)
    {
        return $id.$this->getIdSuffix();
    }

    public function getAddButtonId()
    {
        return $this->suffixId('addBtn');
    }

    public function getSubrowsContainerBlock($fieldName)
    {
        return Mage::app()->getLayout()->getBlockSingleton('udtiership/adminhtml_systemConfigField_v2_simpleCondRates_subrows')
            ->setTemplate('udtiership/system/form_field/v2/simple_cond_rates/subrows.phtml')
            ->setFieldName($fieldName);
    }

}