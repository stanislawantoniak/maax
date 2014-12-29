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


class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tab_Advanced extends Mage_Adminhtml_Block_Widget_Form {

    protected function _prepareForm() {

        $current = Mage::registry("current_campaign");

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('content_fieldset', array('legend' => $this->__('Advanced Options')));

        $fieldset->addField("url", "text", array(
            "label" => $this->__("Campaign URL"),
            "name" => "url",
            "note" => $this->__("This is the URL from where the content will be fetched to show to the subscriber. If you set any, it's YOUR responsability to handle newsletter content in the provided URL."),
        ));


        $this->setForm($form);

        if ($current) {
            $form->setValues($current->getData());
        }

        return parent::_prepareForm();
    }

}
