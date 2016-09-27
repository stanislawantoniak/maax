<?php

class Snowdog_Freshmail_Block_System_Config_Form_Field_Customfields
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    protected $_useInFormRenderer;
    protected $_sourceFieldRenderer;

    public function __construct()
    {
        $this->addColumn('source_field', array(
            'label' => Mage::helper('snowfreshmail')->__('Magento Attribute'),
            'renderer' => $this->_getSourceFieldRenderer(),
            'class' => 'required-entry',
        ));
        $this->addColumn('target_field', array(
            'label' => Mage::helper('snowfreshmail')->__('FreshMail Tag'),
            'style' => 'width:150px',
            'class' => 'input-text required-entry validate-code',
        ));
        $this->addColumn('use_in_form', array(
            'label' => Mage::helper('snowfreshmail')->__('Use in form'),
            'renderer' => $this->_getUseInFormRenderer(),
            'class' => 'required-entry',
        ));

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('snowfreshmail')->__('Add');

        parent::__construct();
    }

    protected function _prepareArrayRow(Varien_Object $row)
    {
        $keys = array(
            $this->_getSourceFieldRenderer()->calcOptionHash($row->getSourceField()),
            $this->_getUseInFormRenderer()->calcOptionHash($row->getUseInForm())
        );
        foreach ($keys as $key) {
            $row->setData('option_extra_attr_' . $key, 'selected="selected"');
        }
    }

    protected function _getSourceFieldRenderer()
    {
        if (null === $this->_sourceFieldRenderer) {
            $this->_sourceFieldRenderer = Mage::app()->getLayout()->createBlock(
                'snowfreshmail/system_config_form_field_sourceField',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_sourceFieldRenderer;
    }

    protected function _getUseInFormRenderer()
    {
        if (null === $this->_useInFormRenderer) {
            $this->_useInFormRenderer = Mage::app()->getLayout()->createBlock(
                'snowfreshmail/system_config_form_field_useinform',
                '',
                array('is_render_to_js_template' => true)
            );
        }

        return $this->_useInFormRenderer;
    }
}
