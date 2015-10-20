<?php

/**
 * Description of Form
 */
class GH_Regulation_Block_Adminhtml_Type_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {
        $helper = Mage::helper('ghregulation');
        $model = Mage::registry('ghregulation_current_type');

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

        $fieldset->addField('regulation_kind_id', 'select', array(
            'name' => 'regulation_kind_id',
            'required' => true,
            'label' => $helper->__('Document kind'),
            'values' => $this->_getKindValues(),
        ));

        $form->setValues($model->getData());
        $this->setForm($form);
        return parent::_prepareForm();
    }

    protected function _getKindValues() {
        $model = Mage::getResourceModel('ghregulation/regulation_kind_collection');
        $array = $model->toArray();
        $out = array(
            '' => Mage::helper('ghregulation')->__(' --- choose document kind --- '),
        );
        if (!empty($array['items'])) {
            foreach ($array['items'] as $item) {
                $out[$item['regulation_kind_id']] = $item['name'];
            }
        }
        return $out;
    }

    protected function _isNew() {
        return !(int)$this->getDataObject()->getId();
    }

}

