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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Recurring extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('fidelitas/campaigns/recurring.phtml');
    }

    protected function _prepareForm() {

        $current = Mage::registry("current_campaign");

        if ($current->getId()) {
            $extraRun = $this->__('This campaign has already runned for %d time(s)', $current->getRunTimes() - $current->getRunTimesLeft());
        } else {
            $extraRun = '';
        }

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('content_fieldset', array('legend' => $this->__('Recurring Profile')));

        $outputFormat = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        $outputFormatDate = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);


        $fieldset->addField("recurring", "select", array(
            "label" => $this->__("Recurring Campaign?"),
            "class" => "required-entry",
            "onchange" => "handlecron()",
            "required" => true,
            "values" => Licentia_Fidelitas_Model_Campaigns::getCronList(),
            "name" => "recurring",
        ));


        $fieldset->addField('deploy_at', 'date', array(
            'name' => 'deploy_at',
            'time' => true,
            'format' => $outputFormat,
            'label' => $this->__('Deploy Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif')
        ));


        $fieldset->addField("recurring_daily", "multiselect", array(
            "label" => $this->__("In Which days?"),
            "values" => Licentia_Fidelitas_Model_Campaigns::getDaysList(),
            "name" => "recurring_daily",
        ));

        $fieldset->addField("recurring_unique", "select", array(
            "label" => $this->__("Unique Receiver?"),
            "note" => $this->__("Use this option if you do not want this campaign to be sent to the same receiver more than once."),
            "values" => array('0' => $this->__('No'), '1' => $this->__('Yes')),
            "name" => "recurring_unique",
        ));

        $fieldset->addField("recurring_day", "select", array(
            "label" => $this->__("In Which day?"),
            "values" => Licentia_Fidelitas_Model_Campaigns::getDaysList(),
            "name" => "recurring_day",
        ));

        $fieldset->addField("recurring_monthly", "select", array(
            "label" => $this->__("In which day?"),
            "values" => Licentia_Fidelitas_Model_Campaigns::getDaysMonthsList(),
            "name" => "recurring_monthly",
        ));

        $fieldset->addField("recurring_month", "select", array(
            "label" => $this->__("In which month?"),
            "values" => Licentia_Fidelitas_Model_Campaigns::getMonthsList(),
            "name" => "recurring_month",
        ));

        $fieldset->addField('recurring_first_run', 'date', array(
            'name' => 'recurring_first_run',
            'time' => true,
            'format' => $outputFormat,
            'label' => $this->__('First Run'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            "note" => $this->__("First time this campaign should run"),
        ));

        $fieldset->addField('run_until', 'date', array(
            'name' => 'run_until',
            'format' => $outputFormatDate,
            'label' => $this->__('End Date'),
            'image' => $this->getSkinUrl('images/grid-cal.gif'),
            "note" => $this->__("How long should this campaing run."),
        ));


        $fieldset->addField("run_times", "text", array(
            "label" => $this->__("Running times"),
            "name" => "run_times",
            "note" => $this->__("How many times should this campaing run" . $extraRun),
        ));

        $this->setForm($form);

        if ($current->getData()) {
            $form->setValues($current->getData());
        } else {
            $date = new Zend_Date(null, $outputFormat);
            $date->addDay(1);
            $form->setValues(array('deploy_at' => $date));
        }

        return parent::_prepareForm();
    }

    public function getTemplateOptions() {

        return Mage::getModel('fidelitas/templates')->getOptionArray();
    }

}
