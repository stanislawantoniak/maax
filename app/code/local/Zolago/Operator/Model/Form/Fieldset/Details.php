<?php
/**
 * building form edit helper
 */
class Zolago_Operator_Model_Form_Fieldset_Details extends Zolago_Common_Model_Form_Fieldset_Abstract {
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
									   'class'		   => 'validate-password',
                                       "maxlength"     => 50
                                   ));
    }
    /**
     * field password confirm
     */
    protected function _addFieldConfirmation() {
        $this->_fieldset->addField('password_confirm', 'password', array(
                                       'name'          => 'confirmation',
                                       'label'         => $this->_helper->__('Confirm password'),
									   'class'		   => 'validate-cpassword',
                                       "maxlength"     => 50
                                   ));


    }


}