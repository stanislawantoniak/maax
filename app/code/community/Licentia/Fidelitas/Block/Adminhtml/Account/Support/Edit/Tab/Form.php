<?php

/**
 * Licentia Fidelitas - Advanced Email and SMS Marketing Automation for E-Goi
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/4.0/
 *
 * @title      Advanced Email and SMS Marketing Automation
 * @category   Marketing
 * @package    Licentia
 * @author     Bento Vilas Boas <bento@licentia.pt>
 * @copyright  Copyright (c) 2012 Licentia - http://licentia.pt
 * @license    Creative Commons Attribution-NonCommercial-NoDerivatives 4.0 International
 */
class Licentia_Fidelitas_Block_Adminhtml_Account_Support_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry('current_account');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("fidelitas_form", array("legend" => $this->__("Information Channel")));

        $fieldset->addField('first_name', "text", array(
            "label" => $this->__('First Name'),
            "required" => true,
            "name" => 'name',
        ));

        $fieldset->addField('last_name', "text", array(
            "label" => $this->__('Last Name'),
            "required" => true,
            "name" => 'name',
        ));

        $eOp = array();
        $eOp[] = array('value' => 'bug', 'label' => $this->__('Bug Report'));
        $eOp[] = array('value' => 'request', 'label' => $this->__('Request'));
        $eOp[] = array('value' => 'other', 'label' => $this->__('Other Information'));


        $fieldset->addField('reason', "select", array(
            "label" => $this->__('Contact Reason'),
            "required" => true,
            'values' => $eOp,
            "name" => 'reason',
        ));

        $fieldset->addField('message', "textarea", array(
            "label" => $this->__('Message'),
            "required" => true,
            "name" => 'message',
            "note" => $this->__('Please be as descriptive as possible'),
        ));


        $fieldset->addField('email', "text", array(
            "label" => $this->__('Email'),
            "required" => true,
            "name" => 'email',
        ));

        $fieldset->addField('cellphone', "text", array(
            "label" => $this->__('Phone'),
            "name" => 'cellphone',
            "note" => $this->__('Also include your country prefix'),
        ));

        $form->setValues($current);

        return parent::_prepareForm();
    }

}
