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
class Licentia_Fidelitas_Block_Adminhtml_Lists_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry("current_list");

        $available = Mage::getModel('fidelitas/lists')->getAvailableStores($current->getId());

        $locked = false;
        if ($current && ($current->getPurpose() == 'admin' || $current->getPurpose() == 'client')) {
            $locked = true;
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("fidelitas_form", array("legend" => $this->__("List Information")));


        $fieldset->addField("nome", "text", array(
            "label" => $this->__("List Name"),
            "class" => "required-entry",
            "required" => true,
            "name" => "nome",
        ));

        if (!$locked) {
            $fieldset->addField("internal_name", "text", array(
                "label" => $this->__("Internal Name"),
                "class" => "required-entry",
                "required" => true,
                "name" => "internal_name",
            ));

            $fieldset->addField("is_active", "select", array(
                "label" => $this->__("Is Active"),
                "options" => array('1' => 'Yes', '0' => 'No',),
                "required" => true,
                "name" => "is_active",
            ));

            $fieldset->addField('store_ids', 'multiselect', array(
                'name' => 'store_ids',
                'label' => $this->__('Store View'),
                'title' => $this->__('Store View'),
                'required' => true,
                'values' => $available,
            ));

            $fieldset->addField("auto", "select", array(
                "label" => $this->__("Auto Add Customer"),
                "options" => array('0' => 'No', '1' => 'Yes'),
                "required" => true,
                "note" => $this->__('Select this option if you wish that every new customer is added to this list automatically.'),
                "name" => "auto",
            ));

            $fieldset->addField("is_default", "select", array(
                "label" => $this->__("Default List"),
                "options" => array('0' => 'No', '1' => 'Yes'),
                "required" => true,
                "note" => $this->__('If no Store defined in current lists or customer created from Admin'),
                "name" => "is_default",
            ));
        }

        if ($current) {

            $currentValues = $current->getData();

            if (count($currentValues) > 0) {
                $currentValues['nome'] = $currentValues['title'];
            }

            $form->setValues($currentValues);

            if (count($currentValues) > 0) {

                $fieldset->addField("listID", "hidden", array(
                    "value" => $currentValues['listnum'],
                    "no_span" => true,
                    "name" => "listID",
                ));
            }
        }
        return parent::_prepareForm();
    }

}
