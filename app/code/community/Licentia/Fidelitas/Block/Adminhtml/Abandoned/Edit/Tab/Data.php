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
class Licentia_Fidelitas_Block_Adminhtml_Abandoned_Edit_Tab_Data extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry('current_abandoned');

        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('page_');

        $type = $this->getRequest()->getParam('type');

        if ($current->getId()) {
            $type = $current->getChannel();
        }

        $fieldset2 = $form->addFieldset('content_fieldset', array('legend' => $this->__('Content')));

        if ($type == 'sms') {
            $fieldset2->addField("from", "select", array(
                "label" => $this->__("Sender"),
                "class" => "required-entry",
                "required" => true,
                "values" => Mage::getModel('fidelitas/senders')->getSenders('sms'),
                "name" => "from",
            ));
        } else {
            $fieldset2->addField("from", "select", array(
                "label" => $this->__("Sender"),
                "class" => "required-entry",
                "required" => true,
                "values" => Mage::getModel('fidelitas/senders')->getSenders('email'),
                "name" => "from",
            ));
        }

        $fieldset2->addField('subject', 'text', array(
            'name' => 'subject',
            'label' => $this->__('Subject'),
            'title' => $this->__('Subject'),
            "required" => true,
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


            $fieldset2->addField('message', 'editor', array(
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

        $this->setForm($form);

        if ($current) {
            $form->addValues($current->getData());
        }

        if ($type == 'sms') {
            $fieldset2->addField('channel', 'hidden', array('value' => 'sms', 'name' => 'channel'));
        }

        return parent::_prepareForm();
    }

}
