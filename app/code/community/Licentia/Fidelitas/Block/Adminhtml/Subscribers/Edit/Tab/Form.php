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
class Licentia_Fidelitas_Block_Adminhtml_Subscribers_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry('current_subscriber');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("fidelitas_form", array("legend" => $this->__("Subscriber Information")));


        $storeS = Mage::getSingleton('fidelitas/lists')->getOptionArray(null, false);

        $fieldset->addField('list', 'select', array(
            'name' => 'list',
            'label' => $this->__('List'),
            'title' => $this->__('List'),
            'options' => $storeS,
            'required' => true,
        ));


        $fieldset->addField('email', "text", array(
            "label" => $this->__('Email'),
            "class" => "required-entry validate-email",
            "required" => true,
            "name" => 'email',
        ));

        $fields = array(
            'first_name',
            'last_name',);

        foreach ($fields as $element) {
            $fieldset->addField($element, "text", array(
                "label" => $this->__(ucwords(str_replace('_', ' ', $element))),
                "name" => $element,
            ));
        }

        $fieldset->addField('cellphone_prefix', "select", array(
            "label" => $this->__('Cellphone Prefix'),
            'values' => Licentia_Fidelitas_Model_Subscribers::getPhonePrefixs(),
            "name" => 'cellphone_prefix',
        ));

        $fieldset->addField('cellphone', "text", array(
            "label" => $this->__('Cellphone Number'),
            "name" => 'cellphone',
        ));

        $form->addValues(array('list' => $this->getRequest()->getParam('list')));

        if ($current->getData()) {
            $form->setValues($current->getData());
        } else {
            $form->setValues(array('status' => '1'));
        }

        return parent::_prepareForm();
    }

}
