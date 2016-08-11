<?php
/**
 * builder for settings fieldset
 */
class Zolago_Pos_Model_Form_Fieldset_Ups extends Zolago_Common_Model_Form_Fieldset_Abstract {
    
    protected function _getHelper() {
        return Mage::helper('zolagopos');
    }
    protected function _addFieldUseUps() {
        $this->_fieldset->addField('use_orbaups', 'select', array(
                                       'name'          => 'use_orbaups',
                                       'label'         => $this->_helper->__("Use this UPS setting"),
                                       'values'		   => Mage::getSingleton("adminhtml/system_config_source_yesno")->toOptionArray(),
                                       'required'      => false,
									   'class'		   => "form-control"
                                   ));

    }
    protected function _addFieldUpsAccount() {
        $this->_fieldset->addField('orbaups_account', 'text', array(
                                       'name'          => 'orbaups_account',
                                       'label'         => $this->_helper->__('UPS License key'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }

    protected function _addFieldUpsLogin() {
        $this->_fieldset->addField('orbaups_login', 'text', array(
                                       'name'          => 'orbaups_login',
                                       'label'         => $this->_helper->__('UPS Login'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }
    protected function _addFieldUpsPassword() {
        $this->_fieldset->addField('orbaups_password', 'password', array(
                                       'name'          => 'orbaups_password',
                                       'label'         => $this->_helper->__('UPS Password'),
                                       'required'      => true,
                                       "maxlength"     => 32,
									   'class'		   => "form-control"
                                   ));

    }
}
