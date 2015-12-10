<?php

/**
 * Edit sql raw query
 */
class GH_Common_Block_Adminhtml_Rawsql_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $helper = Mage::helper('ghcommon');
        $model = Mage::registry('ghcommon_sql');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__('Details'),
        ));
        // query name
        $fieldset->addField('query_name', 'text', array(
            'name' => 'query_name',
            'required' => true,
            'label' => $helper->__('Name'),
        ));
        // sql script
        $fieldset->addField('query_text', 'textarea', array(
            'name' => 'query_text',
            'cols' => 40,
            'rows' => 40,
            'required' => true,
            'label' => $helper->__('Query text'),
        ));

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }


    protected function _isNew() {
        return !(int)$this->getDataObject()->getId();
    }

}

