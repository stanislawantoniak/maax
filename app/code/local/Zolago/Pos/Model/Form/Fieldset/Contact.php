<?php
/**
 * builder for contact fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Contact extends Zolago_Common_Model_Form_Fieldset_Abstract
{
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }

    /**
     * field phone
     */
    protected function _addFieldPhone() {
        $this->_fieldset->addField('phone', 'text', array(
            'name'          => 'phone',
            'label'         => $this->_helper->__('Phone'),
            'required'      => false,
            'class'         => 'form-control',
            "maxlength"     => 50
        ));

    }
}