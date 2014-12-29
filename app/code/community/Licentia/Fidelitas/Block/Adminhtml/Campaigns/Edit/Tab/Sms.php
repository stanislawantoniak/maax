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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Sms extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry("current_campaign");
        $listnum = $this->getRequest()->getParam('listnum');
        if ($current->getId()) {
            $listnum = $current->getListnum();
        }

        $form = new Varien_Data_Form();
        $this->setForm($form);
        $fieldset = $form->addFieldset("fidelitas_form", array("legend" => $this->__("Campaign Information")));

        if (!isset($listnum)) {
            $location = $this->getUrl('*/*/*', array('_current' => true)) . 'listnum/';
            $fieldset->addField('listnum', 'select', array(
                'name' => 'listnum',
                'label' => $this->__('List'),
                'title' => $this->__('List'),
                'required' => true,
                'values' => Mage::getSingleton('fidelitas/lists')->getAllOptions(null, $this->__('Select')),
                "onchange" => "window.location='$location'+this.value",
            ));
        } else {

            $fieldset->addField("internal_name", "text", array(
                "label" => $this->__("Internal Name"),
                "class" => "required-entry",
                "required" => true,
                "name" => "internal_name",
            ));

            $fieldset->addField("from", "select", array(
                "label" => $this->__("Sender"),
                "class" => "required-entry",
                "values" => Mage::getModel('fidelitas/senders')->getSenders('sms'),
                "required" => true,
                "name" => "from",
            ));

            $fieldset->addField("subject", "text", array(
                "label" => $this->__("Subject"),
                "class" => "required-entry",
                "required" => true,
                "name" => "subject",
            ));


            $js = '
        <style type="text/css">#togglemessage, .add-image{ display:none !important;} #message{width:275px !important;height:125px !important; }</style>
<script type="text/javascript">
    function checkChars(field,divHtml){
        var size = 160 - $F(field).length
        if(size<1)
        {
            //$(field).value = $F(field).substring(0,160);
            //size = 0;
        }
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

            $segs = Mage::getSingleton('fidelitas/campaigns')->getSegments(array($listnum));
            if (count($segs) > 0) {
                $fieldset->addField("segments_origin", "select", array(
                    "label" => $this->__("Specifiy Segments from..."),
                    "name" => "segments_origin",
                    "onchange" => "handlesegments()",
                    "values" => array('store' => 'Store Extension', 'egoi' => 'E-Goi.com Segments'),
                ));


                $fieldset->addField('egoi_segments', 'multiselect', array(
                    'name' => 'egoi_segments[]',
                    'label' => $this->__('E-Goi Segments'),
                    'title' => $this->__('E-Goi Segments'),
                    'values' => $segs,
                    'note' => $this->__('Segments in your E-Goi account'),
                ));
            }

            $fieldset->addField('segments_ids', 'multiselect', array(
                'name' => 'segments_ids[]',
                'label' => $this->__('Customer Segment'),
                'title' => $this->__('Customer Segment'),
                'required' => true,
                'values' => Mage::getSingleton('fidelitas/segments')->getOptionArray(),
            ));
        }

        if ($current->getData()) {
            $form->setValues($current->getData());
        }

        if ($listnum) {
            $fieldset->addField('listnum', 'hidden', array('value' => $listnum, 'name' => 'listnum'));
        }$fieldset->addField('channel', 'hidden', array('value' => 'SMS', 'name' => 'channel'));
        return parent::_prepareForm();
    }

}
