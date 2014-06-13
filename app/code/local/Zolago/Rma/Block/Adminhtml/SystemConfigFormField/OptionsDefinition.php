<?php

require_once(Mage::getModuleDir('Block', 'Unirgy_Rma'). DS . 'Block' . DS . 'Adminhtml' . DS . 'SystemConfigFormField' . DS . 'OptionsDefinition.php');
class Zolago_Rma_Block_Adminhtml_SystemConfigFormField_OptionsDefinition 
	extends Unirgy_Rma_Block_Adminhtml_SystemConfigFormField_OptionsDefinition
{
    protected $_element = null;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('zolagorma/system/form_field/options_definition.phtml');
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