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
                                       'label'         => $this->_helper->__('Use this DHL setting'),
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
									   'class'		   => "form-control disable_dhl"
                                   ));

    }

    protected function _addFieldDhlLogin() {
        $this->_fieldset->addField('dhl_login', 'text', array(
                                       'name'          => 'dhl_login',
                                       'label'         => $this->_helper->__('DHL Login'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control disable_dhl"
                                   ));

    }
    protected function _addFieldDhlPassword() {
        $this->_fieldset->addField('dhl_password', 'password', array(
                                       'name'          => 'dhl_password',
                                       'label'         => $this->_helper->__('DHL Password'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control disable_dhl"
                                   ));

    }
    protected function _addFieldDhlEcas() {
        $this->_fieldset->addField('dhl_ecas', 'text', array(
                                       'name'          => 'dhl_ecas',
                                       'label'         => $this->_helper->__('eCas Id'),
                                       'required'      => false,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }

    protected function _addFieldDhlTerminal() {
        $this->_fieldset->addField('dhl_terminal', 'text', array(
                                       'name'          => 'dhl_terminal',
                                       'label'         => $this->_helper->__('Terminal'),
                                       'required'      => false,
                                       "maxlength"     => 2,
									   'class'		   => "form-control"
                                   ));

    }
    protected function _addFieldDhlCheckButton() {
        $this->_fieldset->addType('check_button',Mage::getConfig()->getBlockClassName('orbashipping/dhl_check'));
        $this->_fieldset->addField('dhl_check_button', 'check_button', array(
                                       'name'          => 'dhl_check_button',
                                       'label'         => '',
                                   ));

    }
}
