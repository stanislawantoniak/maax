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
class Licentia_Fidelitas_Block_Adminhtml_Templates_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry('current_template');

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('content_fieldset', array('legend' => $this->__('Content'), 'class' => 'fieldset-wide'));

        $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                array('tab_id' => $this->getTabId())
        );


        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => $this->__('Internal Name'),
            'title' => $this->__('Internal Name'),
            "required" => true,
        ));

        $fieldset->addField('status', "select", array(
            "label" => $this->__('Status'),
            "options" => array('1' => $this->__('Active'), '0' => $this->__('Inactive')),
            "name" => 'status',
        ));
        
        $contentField = $fieldset->addField('message', 'editor', array(
            'name' => 'message',
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

}
