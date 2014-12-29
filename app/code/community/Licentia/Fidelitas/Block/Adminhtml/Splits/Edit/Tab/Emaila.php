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
class Licentia_Fidelitas_Block_Adminhtml_Splits_Edit_Tab_Emaila extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('fidelitas/campaigns/edit.phtml');
    }

    public function getEditMode() {
        $campaign = Mage::registry('current_split');

        if ($campaign->getId())
            return true;
        return false;
    }

    protected function _prepareForm() {

        $current = Mage::registry('current_split');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('content_fieldset', array('legend' => $this->__('Content'), 'class' => 'fieldset-wide'));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array('tab_id' => $this->getTabId())
        );

        $fieldset->addField('subject_a', 'text', array(
            'name' => 'subject_a',
            'label' => $this->__('Subject A'),
            'title' => $this->__('Subject A'),
            "required" => true,
        ));

        $fieldset->addField("sender_a", "select", array(
            "label" => $this->__("Sender A"),
            "class" => "required-entry",
            "required" => true,
            "values" => Mage::getModel('fidelitas/senders')->getSenders('email'),
            "name" => "sender_a",
        ));

        $contentField = $fieldset->addField('message_a', 'editor', array(
            'name' => 'message_a',
            'style' => 'height:36em;',
            'required' => true,
            'config' => $wysiwygConfig
        ));

        // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
                ->setTemplate('cms/page/edit/form/renderer/content.phtml');
        $contentField->setRenderer($renderer);

        $this->setForm($form);

        if ($current) {
            $form->setValues($current->getData());
        }

        return parent::_prepareForm();
    }

    public function getTemplateOptions() {

        return Mage::getModel('fidelitas/templates')->getOptionArray();
    }

}
