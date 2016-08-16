<?php
/**
 * building form edit helper
 */
class ZolagoOs_IAIShop_Model_Form_Fieldset_Details extends Zolago_Common_Model_Form_Fieldset_Abstract {
    protected function _getHelper() {
        return Mage::helper('zosiaishop');
    }

    protected function _addFieldId() {
        $this->_fieldset->addField('id', 'text', array(
            'name'          => 'id',
            'label'         => $this->_helper->__('Store ID'),
            'class'         => 'form-control',
            "maxlength"     => 50
        ));

    }

    protected function _addFieldUrl() {
        $this->_fieldset->addField('url', 'text', array(
            'name'          => 'url',
            'label'         => $this->_helper->__('Url'),
            'class'         => 'form-control',
            'required'      => true,
            "maxlength"     => 100
        ));
    }

    protected function _addFieldLogin() {
        $this->_fieldset->addField('login', 'text', array(
            'name'          => 'login',
            'label'         => $this->_helper->__('Login'),
            'class'		    => 'form-control',
            'required'      => true,
            "maxlength"     => 50
        ));
    }

    protected function _addFieldPass() {
        $this->_fieldset->addField('pass', 'password', array(
            'name'          => 'pass',
            'label'         => $this->_helper->__('Password'),
            'class'		    => 'validate-password form-control',
            "maxlength"     => 50,
            '' => "<p>&nbsp;&nbsp;&nbsp;&nbsp;" . $this->_helper->__('Leave empty for no change') . "</p>"
        ));
    }


}