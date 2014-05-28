<?php
/**
 * builder for settings fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Dhl extends Zolago_Common_Model_Form_Fieldset_Abstract {
    
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }
    protected function _addFieldUseDhl() {
        $this->_fieldset->addField('use_dhl', 'select', array(
                                       'name'          => 'use_dhl',
                                       'label'         => $this->_helper->__('Use DHL'),
                                       'values'		   => Mage::getSingleton("adminhtml/system_config_source_yesno")->toOptionArray(),
                                       'required'      => false,
									   'class'		   => "form-control"
                                   ));

    }
    protected function _addFieldDhlAccount() {
        $this->_fieldset->addField('dhl_account', 'text', array(
                                       'name'          => 'dhl_account',
                                       'label'         => $this->_helper->__('DHL Account'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }

    protected function _addFieldDhlLogin() {
        $this->_fieldset->addField('dhl_login', 'text', array(
                                       'name'          => 'dhl_login',
                                       'label'         => $this->_helper->__('DHL Login'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }
    protected function _addFieldDhlPassword() {
        $this->_fieldset->addField('dhl_password', 'password', array(
                                       'name'          => 'dhl_password',
                                       'label'         => $this->_helper->__('DHL Password'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }

}
