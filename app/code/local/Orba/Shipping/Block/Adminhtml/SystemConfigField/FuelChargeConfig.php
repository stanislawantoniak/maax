<?php

class Orba_Shipping_Block_Adminhtml_SystemConfigField_FuelChargeConfig extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        if (!$this->getTemplate()) {
            $this->setTemplate('orbashipping/system/form_field/fuel_charge_config.phtml');
        }
    }

    protected function _prepareLayout()
    {
        //$this->getLayout()->getBlock('head')->addItem('skin_css','../../../frontend/default/udropship/plugins/jquery-ui/jquery-ui-1.10.2.custom.css');
        //$this->getLayout()->getBlock('head')->addItem('skin_js','../../../frontend/default/udropship/plugins/jquery-ui/jquery-ui-1.10.2.custom.min.js');
        $this->getLayout()->getBlock('head')->addJs('orbashipping/carrier/fuelCharge.js');

        return parent::_prepareLayout();
    }
    public function getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        return $this->_getElementHtml($element);
    }
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        $html = $this->_toHtml();
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
}