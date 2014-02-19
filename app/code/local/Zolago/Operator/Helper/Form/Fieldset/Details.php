<?php
/**
 * building form edit helper
 */
class Zolago_Operator_Helper_Form_Fieldset_Details extends Zolago_Common_Helper_Form_Fieldset_Abstract {
    protected function _getHelper() {
        return Mage::helper('zolagooperator');
    }
    /**
     * field phone
     */
    protected function _addFieldPhone() {
        $this->_fieldset->addField('phone', 'text', array(
                                       'name'          => 'phone',
                                       'label'         => $this->_helper->__('Phone'),
                                       'class'         => 'validate-phone-number',
                                       "maxlength"     => 50
                                   ));

    }
    /**
     * field email
     */
    protected function _addFieldEmail() {
        $this->_fieldset->addField('email', 'text', array(
                                       'name'          => 'email',
                                       'label'         => $this->_helper->__('Email'),
                                       'class'         => 'validate-email',
                                       'required'      => true,
                                       "maxlength"     => 100
                                   ));


    }

    /**
     * field password
     */
    protected function _addFieldPassword() {
        $this->_fieldset->addField('password', 'password', array(
                                       'name'          => 'password',
                                       'label'         => $this->_helper->__('Password'),
                                       'required'      => true,
                                       "maxlength"     => 50
                                   ));
    }
    /**
     * field password confirm
     */
    protected function _addFieldPasswordConfirm() {
        $this->_fieldset->addField('password_confirm', 'password', array(
                                       'name'          => 'password_confirm',
                                       'label'         => $this->_helper->__('Confirm password'),
                                       'required'      => true,
                                       "maxlength"     => 50
                                   ));


    }


}