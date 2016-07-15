<?php
class Zolago_ManaProFilterAdmin_Block_Card_General extends ManaPro_FilterAdmin_Block_Card_General
{
    /**
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $prep = parent::_prepareForm();
// form - collection of fieldsets
        $form = new Varien_Data_Form(array(
            'id' => 'mf_general',
            'html_id_prefix' => 'mf_general_',
            'use_container' => true,
            'method' => 'post',
            'action' => $this->getUrl('*/*/save', array('_current' => true)),
            'field_name_suffix' => 'fields',
            'model' => $this->getModel(),
        ));
        /** @noinspection PhpUndefinedMethodInspection */
        Mage::helper('mana_core/js')->options('edit-form', array('subforms' => array('#mf_general' => '#mf_general')));

// fieldset - collection of fields
        /** @noinspection PhpParamsInspection */
        $fieldset = $form->addFieldset('mfs_general', array(
            'title' => $this->__('General Information'),
            'legend' => $this->__('General Information'),
        ));
        $fieldset->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_fieldset'));

        $field = $fieldset->addField('display', 'checkbox', array(
            'label' => $this->__('Use colors and images'),
            'name' => 'display',
            'required' => true,
            'checked' => $this->getModel()->getData("display") == "colors",
        ));
        $field->setRenderer($this->getLayout()->getBlockSingleton('mana_admin/crud_card_field'));

        $this->setForm($form);

        return $prep;
    }
}
