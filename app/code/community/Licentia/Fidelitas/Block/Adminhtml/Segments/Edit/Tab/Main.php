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
class Licentia_Fidelitas_Block_Adminhtml_Segments_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function getTabLabel() {
        return $this->__('General');
    }

    public function getTabTitle() {
        return $this->__('General');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    protected function _prepareForm() {

        $current = Mage::registry("current_segment_rule");

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("fidelitas_form", array("legend" => $this->__("Segment Information")));

        $fieldset->addField("name", "text", array(
            "label" => $this->__("Segment Name"),
            "class" => "required-entry",
            "required" => true,
            "name" => "name",
        ));

        $fieldset->addField("is_active", "select", array(
            "label" => $this->__("Is Active"),
            "options" => array('1' => $this->__('Yes'), '0' => $this->__('No')),
            "required" => true,
            "name" => "is_active",
        ));

        $fieldset->addField("type", "select", array(
            "label" => $this->__("Segment Type"),
            "options" => array('customers' => $this->__('Registered Customers'), 'visitors' => $this->__('Guest Users'), 'both' => $this->__('Registered Customers and Guest Users')),
            "name" => "type",
            "note" => $this->__("Can not be changed after."),
            "disabled" => $current->getId() ? true : false,
        ));

        $fieldset->addField("description", "textarea", array(
            "label" => $this->__("Description"),
            "name" => "description",
        ));

        $fieldset->addField("cron", "select", array(
            "label" => $this->__("Update"),
            'note' => $this->__('Use this option if you want to keep track of subscribers evolution in this segment.'),
            "options" => array('0' => $this->__('No'), 'd' => $this->__('Daily'), 'w' => $this->__('Weekly'), 'm' => $this->__('Monthly')),
            "name" => "cron",
        ));

        if ($current) {
            $currentValues = $current->getData();
            $form->setValues($currentValues);
        }
        return parent::_prepareForm();
    }

}
