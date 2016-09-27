<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */
class Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Store
    extends Magpleasure_Blog_Block_Adminhtml_Attribute_Edit_Tabs_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    const STORE_KEYS = 'mpblog_attribute_update_stores';

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset(
            'add_store_legend',
            array('legend' => $this->_helper()->__('Add Store View'))
        );

        $fieldset->addField('add_store', 'checkbox', array(
            'label' => $this->_helper()->__("Add Store View"),
            'required' => false,
            'name' => 'add_store',
            'checked'   => $this->_isChecked('add_store'),
            'onchange' => "checkboxChanged('add_store');",
        ));

        $fieldset->addField('add_store_values', 'multiselect', array(
            'label' => $this->_helper()->__('Store View to add'),
            'required' => false,
            'name' => 'add_store_values[]',
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'disabled' => !$this->_isChecked('add_store'),
        ));

        $fieldset = $form->addFieldset(
            'remove_store_legend',
            array('legend' => $this->_helper()->__('Remove Store View'))
        );

        $fieldset->addField('remove_store', 'checkbox', array(
            'label' => $this->_helper()->__("Remove Store View"),
            'required' => false,
            'name' => 'remove_store',
            'checked'   => $this->_isChecked('remove_store'),
            'onchange' => "checkboxChanged('remove_store');",
        ));

        $fieldset->addField('remove_store_values', 'multiselect', array(
            'label' => $this->_helper()->__('Store View to remove'),
            'required' => false,
            'name' => 'remove_store_values[]',
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
            'disabled' => !$this->_isChecked('remove_store'),
        ));

        $form->setUseContainer(false);
        $form->setValues($this->_getValues());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("Visible in Store View");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
