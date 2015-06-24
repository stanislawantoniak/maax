<?php

/**
 * builder for settings fieldset
 */
class GH_Dhl_Model_Form_Fieldset_Dhl extends Zolago_Common_Model_Form_Fieldset_Abstract
{

    protected function _getHelper()
    {
        return Mage::helper('ghdhl');
    }

    protected function _addFieldDhlAccount()
    {
        $this->_fieldset->addField('dhl_account', 'text', array(
            'name' => 'dhl_account',
            'label' => $this->_helper->__('DHL Account'),
            'required' => true,
            "maxlength" => 32,
            'class' => "form-control"
        ));

    }

    protected function _addFieldDhlLogin()
    {
        $this->_fieldset->addField('dhl_login', 'text', array(
            'name' => 'dhl_login',
            'label' => $this->_helper->__('DHL Login'),
            'required' => true,
            "maxlength" => 32,
            'class' => "form-control"
        ));

    }

    protected function _addFieldDhlPassword()
    {
        $this->_fieldset->addField('dhl_password', 'password', array(
            'name' => 'dhl_password',
            'label' => $this->_helper->__('DHL Password'),
            'required' => true,
            "maxlength" => 32,
            'class' => "form-control"
        ));

    }

    protected function _addFieldComment()
    {
        $this->_fieldset->addField('comment', 'textarea', array(
            'name' => 'comment',
            'label' => $this->_helper->__('Comment'),
            'required' => false,
            "maxlength" => 700,
            'class' => "form-control"
        ));

    }
}
