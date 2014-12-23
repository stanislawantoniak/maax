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
class Licentia_Fidelitas_Block_Adminhtml_Abandoned_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('fidelitas/campaigns/edit.phtml');
    }

    public function getEditMode() {
        $campaign = Mage::registry('current_abandoned');

        if ($campaign->getCampaignId()) {
            return true;
        }

        return false;
    }

    protected function _prepareForm() {

        $current = Mage::registry('current_abandoned');

        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('content_fieldset', array('config' => Mage::getSingleton('cms/wysiwyg_config')->getConfig(), 'legend' => $this->__('fidelitas'), 'class' => 'fieldset-wide'));


        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array('tab_id' => $this->getTabId())
        );

        $contentField = $fieldset->addField('message', 'editor', array(
            'name' => 'message',
            'label' => $this->__('Template Body'),
            'title' => $this->__('Template Body'),
            'style' => 'height:36em;',
            'config' => $wysiwygConfig,
            'wysiwyg' => true,
            'required' => true,
        ));
        // Setting custom renderer for content field to remove label column
        $renderer = $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
                ->setTemplate('cms/page/edit/form/renderer/content.phtml');
        $contentField->setRenderer($renderer);

        $this->setForm($form);

        if ($current->getData()) {
            $form->setValues($current->getData());
        }

        $fieldset->addField('channel', 'hidden', array('value' => 'email', 'name' => 'channel'));

        return parent::_prepareForm();
    }

    public function getTemplateOptions() {

        return Mage::getModel('fidelitas/templates')->getOptionArray();
    }

}
