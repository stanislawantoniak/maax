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
class Licentia_Fidelitas_Block_Adminhtml_Splits_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry('current_split');

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("fidelitas_form", array("legend" => $this->__("Campaign Information")));

        $fieldset->addField('name', "text", array(
            "label" => $this->__("Internal Name"),
            "class" => "required-entry",
            "required" => true,
            "name" => "name",
        ));

        $fieldset->addField("active", "select", array(
            "label" => $this->__("Active"),
            "class" => "required-entry",
            "required" => true,
            "values" => array('0' => $this->__('No'), '1' => $this->__('Yes')),
            "name" => "active",
        ));

        $fieldset->addField('listnum', 'select', array(
            'name' => 'listnum',
            'label' => $this->__('List'),
            'title' => $this->__('List'),
            'required' => true,
            'values' => Mage::getSingleton('fidelitas/lists')->getAllOptions(),
        ));


        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $fieldset->addField('deploy_at', 'date', array(
            'name' => 'deploy_at',
            'time' => true,
            'required' => true,
            'format' => $outputFormat,
            'label' => $this->__('Test Deploy Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif')
        ));


        $fieldset->addField('days', 'select', array(
            'name' => 'days',
            'label' => $this->__('Send General Campaign after X days'),
            'title' => $this->__('Send General Campaign after X days'),
            'required' => true,
            'options' => array_combine(range(1, 10), range(1, 10)),
        ));

        $fieldset->addField('percentage', 'select', array(
            'name' => 'percentage',
            'label' => $this->__('Percentage Emails Send Test'),
            'title' => $this->__('Percentage Emails Send Test'),
            'required' => true,
            'options' => array_combine(range(5, 40, 5), range(5, 40, 5)),
        ));


        $fieldset->addField('winner', 'select', array(
            'name' => 'winner',
            'label' => $this->__('How to determine winner'),
            'title' => $this->__('How to determine winner'),
            'required' => true,
            'options' => Mage::getModel('fidelitas/splits')->getWinnerOptions(),
        ));


        $fieldset->addField('segment_id', 'select', array(
            'name' => 'segment_id',
            'label' => $this->__('Segment'),
            'title' => $this->__('Segment'),
            'required' => true,
            'values' => Mage::getSingleton('fidelitas/segments')->getOptionArray(),
        ));

        if ($current->getData()) {
            $form->setValues($current->getData());
        }

        $fieldset->addField('channel', 'hidden', array('value' => 'email', 'name' => 'channel'));

        return parent::_prepareForm();
    }

}
