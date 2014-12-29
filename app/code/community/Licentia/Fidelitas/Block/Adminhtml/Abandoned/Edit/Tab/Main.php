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
class Licentia_Fidelitas_Block_Adminhtml_Abandoned_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry('current_abandoned');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('page_');

        $type = $this->getRequest()->getParam('type');

        $fieldset = $form->addFieldset('params_fieldset', array('legend' => $this->__('Settings')));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Name'),
            'title' => $this->__('Name'),
            "required" => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => $this->__('Description'),
            'title' => $this->__('Description'),
        ));

        $fieldset->addField('days', "select", array(
            "label" => $this->__('Send After X Days'),
            "options" => array_combine(range(0, 30), range(0, 30)),
            "name" => 'days',
        ));

        $fieldset->addField('hours', "select", array(
            "label" => $this->__('Send After X Hours...'),
            "options" => array_combine(range(0, 23), range(0, 23)),
            "name" => 'hours',
        ));

        $fieldset->addField('minutes', "select", array(
            "label" => $this->__('Send After X Minutes...'),
            "options" => array_combine(range(0, 60, 5), range(0, 60, 5)),
            "name" => 'minutes',
        ));

        $fieldset->addField('groups', 'multiselect', array(
            'name' => 'groups[]',
            'label' => $this->__('Customer Groups'),
            'title' => $this->__('Customer Groups'),
            'required' => true,
            'values' => Mage::getResourceModel('customer/group_collection')->toOptionArray(),
        ));

        $fieldset->addField('stores', 'multiselect', array(
            'name' => 'stores[]',
            'label' => $this->__('Subscribers From'),
            'required' => true,
            'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(),
        ));

        $outputFormatDate = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name' => 'from_date',
            'format' => $outputFormatDate,
            'label' => $this->__('Active From Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));
        $fieldset->addField('to_date', 'date', array(
            'name' => 'to_date',
            'format' => $outputFormatDate,
            'label' => $this->__('Active To Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
        ));

        $fieldset->addField('is_active', "select", array(
            "label" => $this->__('Status'),
            "options" => array('1' => $this->__('Active'), '0' => $this->__('Inactive')),
            "name" => 'is_active',
        ));
        $this->setForm($form);

        if ($current) {
            $form->addValues($current->getData());
        }

        if ($type == 'sms') {
            $fieldset->addField('channel', 'hidden', array('value' => 'sms', 'name' => 'channel'));
        }

        return parent::_prepareForm();
    }

}
