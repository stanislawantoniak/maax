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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Links extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
    }

    protected function _prepareForm() {

        $current = Mage::registry("current_campaign");

        if ($current->getId()) {
            $extraRun = $this->__('This campaign has already runned for %d time(s)', $current->getRunTimes() - $current->getRunTimesLeft());
        } else {
            $extraRun = '';
        }

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('content_fieldset', array('legend' => $this->__('Links Top')));

        $fields = array('link_referer_top', 'link_view_top', 'link_edit_top', 'link_print_top', 'link_social_networks_top');

        foreach ($fields as $field) {
            $title = str_replace('_top', '', $field);
            $title = ucwords(str_replace('_', ' ', $title));

            $fieldset->addField($field, "checkbox", array(
                "label" => $this->__("Show " . $title),
                "value" => 1,
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                "name" => $field,
            ))->setIsChecked($current->getData() ? $current->getData() : 1);
        }

        $fieldsetBottom = $form->addFieldset('content_fieldset_bottom', array('legend' => $this->__('Links Bottom')));

        $fields = array('link_referer_bottom', 'link_view_bottom', 'link_edit_bottom', 'link_print_bottom', 'link_social_networks_bottom');

        foreach ($fields as $field) {

            $title = str_replace('_bottom', '', $field);
            $title = ucwords(str_replace('_', ' ', $title));

            $fieldsetBottom->addField($field, "checkbox", array(
                "label" => $this->__("Show " . $title),
                "value" => 1,
                'onclick' => 'this.value = this.checked ? 1 : 0;',
                "name" => $field,
            ))->setIsChecked($current->getData() ? $current->getData() : 1);
        }

        $this->setForm($form);

        if ($current->getData()) {
            $form->setValues($current->getData());
        }

        return parent::_prepareForm();
    }

    public function getTemplateOptions() {

        return Mage::getModel('fidelitas/templates')->getOptionArray();
    }

}
