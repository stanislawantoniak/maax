<?php

/**
 * Description of Form
 */
class GH_Regulation_Block_Adminhtml_Kind_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $helper = Mage::helper('ghregulation');
        $model = Mage::registry('ghregulation_current_kind');

        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getData('action'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => $helper->__('Details'),
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'required' => true,
            'label' => $helper->__('Name'),
        ));

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }


    protected function _isNew() {
        return !(int)$this->getDataObject()->getId();
    }

}

