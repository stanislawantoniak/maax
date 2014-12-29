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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        $this->_objectId = 'id';
        $this->_blockGroup = "fidelitas";
        $this->_controller = 'adminhtml_campaigns';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save'));
        $this->_updateButton('delete', 'label', $this->__('Delete'));

        $campaign = Mage::registry('current_campaign');
        $listnum = $this->getRequest()->getParam('listnum');
        if ($campaign->getId()) {
            $listnum = $campaign->getListnum();
        }
        if (!$listnum) {
            $this->_removeButton('save');
            $this->_removeButton('reset');
            return;
        }
        if ($campaign) {

            if ($campaign->getRecurring() == '0') {
                $emailUrl = $this->getUrl('*/fidelitas_followup/new', array('type' => 'email', 'cid' => $campaign->getId()));
                $this->addButton('followup_email', array('label' => $this->__('Email F'),
                    'class' => "add",
                    'title' => "Email Follow Up",
                    'onclick' => "window.location='$emailUrl';"));

                $smsUrl = $this->getUrl('*/fidelitas_followup/new', array('type' => 'sms', 'cid' => $campaign->getId()));
                $this->addButton('followup_sms', array('label' => $this->__('SMS F'),
                    'class' => "add",
                    'title' => "SMS Follow Up",
                    'onclick' => "window.location='$smsUrl';"));
            }

            if ($campaign->getRecurring() != '0' && $campaign->getLocalStatus() != 'finished') {
                $cancelUrl = $this->getUrl('*/*/cancel', array('id' => $campaign->getId()));
                $text = $this->__('Cancel this campaign? This action can not be undone');

                $this->addButton('cancel_campaign', array('label' => $this->__('Cancel Campaign'),
                    'onclick' => "if(!confirm('$text')){return false;}; window.location='$cancelUrl';"));
            }

            if (strtolower($campaign->getChannel()) == 'email') {

                $previewUrl = $this->getUrl('*/*/preview', array('id' => $campaign->getId()));

                $this->_addButton('preview', array(
                    'label' => $this->__('Preview'),
                    'onclick' => "window.open('$previewUrl'); return false;",
                        )
                );
            }
            $text = $this->__('Start the sending process now?');

            $this->addButton('send', array('label' => $this->__('Save & Send'),
                'class' => 'save saveandsendbutton ',
                'onclick' => "if(!confirm('$text')){return false;}; saveAndSend()"));
        }



        if ($campaign->getLocalStatus() == 'finished') {
            $this->_removeButton('save');
            $this->_removeButton('send');

            $this->addButton('duplicate', array('label' => $this->__('Duplicate & Save'),
                'class' => 'save',
                'onclick' => "editForm.submit($('edit_form').action + 'op/duplicate/')"));
        } else {
            $this->_addButton("saveandcontinuebarcode", array(
                "label" => $this->__("Save and Continue Edit"),
                "onclick" => "saveAndContinueEdit()",
                "class" => "save",
                    ), -100);

            $this->_formScripts[] = " function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";
        }

        $this->_formScripts[] = "

            function saveAndSend(){ editForm.submit($('edit_form').action + 'op/send/') }

            function toggleEditor() {
                if (tinyMCE.getInstanceById('block_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'block_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'block_content');
                }
            }";
    }

    public function getHeaderText() {
        $listnum = $this->getRequest()->getParam('listnum');
        $campaign = Mage::registry('current_campaign');


        if ($campaign->getId()) {
            $listnum = $campaign->getListnum();
            $list = Mage::getModel('fidelitas/lists')->load($listnum, 'listnum');
            return $this->__($list->getInternalName() . ' / ' . $this->htmlEscape($campaign->getInternalName()));
        } else {
            $list = Mage::getModel('fidelitas/lists')->load($listnum, 'listnum');
            return $this->__($list->getInternalName() . ' / ' . 'New Campaign');
        }
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

}
