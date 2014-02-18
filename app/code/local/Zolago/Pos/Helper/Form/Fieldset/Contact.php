<?php
/**
 * builder for contact fieldset
 */
class Zolago_Pos_Helper_Form_Fieldset_Contact extends Zolago_Pos_Helper_Form_Fieldset 
{
    protected function _addFieldPhone() {
        $this->_fieldset->addField('phone', 'text', array(
                                       'name'          => 'phone',
                                       'label'         => $this->_helper->__('Phone'),
                                       'required'      => true,
                                       'class'         => 'validate-phone-number',
                                       "maxlength"     => 50
                                   ));

    }
    protected function _addFieldEmail() {
        $this->_fieldset->addField('email', 'text', array(
                                       'name'          => 'email',
                                       'label'         => $this->_helper->__('Email'),
                                       'class'         => 'validate-email',
                                       "maxlength"     => 100
                                   ));


    }

}