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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Email extends Mage_Adminhtml_Block_Widget_Form {

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
                "note" => $this->__('This name helps you internally to identify this campaign'),
                "name" => "internal_name",
            ));

            $fieldset->addField("from", "select", array(
                "label" => $this->__("Sender"),
                "class" => "required-entry",
                "required" => true,
                "values" => Mage::getModel('fidelitas/senders')->getSenders('email'),
                "name" => "from",
            ));

            $fieldset->addField("subject", "text", array(
                "label" => $this->__("Subject"),
                "class" => "required-entry",
                "required" => true,
                "name" => "subject",
            ));

            $temps = Mage::getModel('fidelitas/egoi')->getHeaderFooterTemplates();
            if (count($temps) > 0) {
                $fieldset->addField('header_footer_template', 'select', array(
                    'name' => 'header_footer_template',
                    'label' => $this->__('Header & Footer Template'),
                    'title' => $this->__('Header & Footer Template'),
                    'options' => $temps,
                ));
            }

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
                'note' => $this->__('Please note that this campaign will be sent to ALL subscribers from ALL selected segments, and not only to subscribers that are in all selected segments.'),
                'values' => Mage::getSingleton('fidelitas/segments')->getOptionArray(),
            ));
        }
        if ($current->getData()) {
            $form->setValues($current->getData());
        }

        if ($listnum) {
            $fieldset->addField('listnum', 'hidden', array('value' => $listnum, 'name' => 'listnum'));
        }
        $fieldset->addField('channel', 'hidden', array('value' => 'email', 'name' => 'channel'));
        return parent::_prepareForm();
    }

}
