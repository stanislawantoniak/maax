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
class Licentia_Fidelitas_Block_Adminhtml_Followup_Edit_Tab_Main extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry("current_followup");

        $type = $this->getRequest()->getParam('type');

        if ($current->getId()) {
            $type = $current->getChannel();
        }


        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('content_fieldset', array('legend' => $this->__('Follow Ups')));

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

        $fieldset->addField('subject', "text", array(
            "label" => $this->__("Subject"),
            "class" => "required-entry",
            "required" => true,
            "note" => $this->__('the {{subject}} tag will be replaced by the original campaign subject'),
            "name" => "subject",
        ));


        $fieldset->addField("recipients_options", "multiselect", array(
            "label" => $this->__("Send to subscribers that..."),
            "class" => "required-entry",
            "values" => Mage::getModel('fidelitas/followup')->getOptionValues(),
            "name" => "recipients_options[]",
        ));

        $fieldset->addField('days', 'select', array(
            'name' => 'days',
            'label' => $this->__('Send after X days'),
            'title' => $this->__('Send after X days'),
            'required' => true,
            'options' => array_combine(range(1, 10), range(1, 10)),
        ));


        $fieldset->addField('segment_id', 'select', array(
            'name' => 'segment_id',
            'label' => $this->__('Segment'),
            'title' => $this->__('Segment'),
            'required' => true,
            'values' => Mage::getSingleton('fidelitas/segments')->getOptionArray(),
        ));

        if ($type == 'sms') {

            $js = '
        <style type="text/css">#togglepage_message, .add-image{ display:none !important;} #message{width:275px !important;height:125px !important; }</style>
<script type="text/javascript">
    function checkChars(field,divHtml){
        var size = 160 - $F(field).length
        $(divHtml).update(size);
    }
</script>';

            $wysiwygConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig(
                    array('tab_id' => $this->getTabId())
            );

            $wysiwygConfig->setData('hidden', 1);
            $wysiwygConfig->setData('add_images', false);


            $fieldset->addField('message', 'editor', array(
                "label" => $this->__("Message"),
                "class" => "required-entry",
                "required" => true,
                "onkeyup" => "checkChars(this,'charsLeft')",
                "name" => "message",
                'config' => $wysiwygConfig,
                'wysiwyg' => true,
                'required' => true,
                "note" => $this->__('160 characters limit. [<span id="charsLeft">160</span> left]'),
            ))->setAfterElementHtml($js);
        }

        if ($current->getData()) {
            $form->setValues($current->getData());
        }

        $this->setForm($form);

        if ($type == 'sms') {
            $fieldset->addField('channel', 'hidden', array('value' => 'sms', 'name' => 'channel'));
        }

        return parent::_prepareForm();
    }

}
