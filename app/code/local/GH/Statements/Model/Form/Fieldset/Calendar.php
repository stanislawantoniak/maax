<?php

/**
 * builder for settings fieldset
 */
class GH_STatements_Model_Form_Fieldset_Calendar extends Zolago_Common_Model_Form_Fieldset_Abstract
{

    protected function _getHelper()
    {
        return Mage::helper('ghstatements');
    }

    protected function _addFieldName()
    {
        $this->_fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->_helper->__('Calendar name'),
            'required' => true,
            "maxlength" => 32,
            'class' => "form-control"
        ));

    }
}
