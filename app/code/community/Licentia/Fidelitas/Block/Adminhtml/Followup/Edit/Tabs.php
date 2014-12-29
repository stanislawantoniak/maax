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
class Licentia_Fidelitas_Block_Adminhtml_Followup_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("fidelitas_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle($this->__("Follow Up Information"));
    }

    protected function _beforeToHtml() {

        $current = Mage::registry('current_followup');
        $type = $this->getRequest()->getParam('type');

        if ($current->getChannel()) {
            $type = strtolower($current->getChannel());
        }


        $this->addTab("main_section", array(
            "label" => $this->__("Settings"),
            "title" => $this->__("Settings"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_followup_edit_tab_main")->toHtml(),
        ));

        if ( $type == 'email') {
            $this->addTab("form_section", array(
                "label" => $this->__("Message"),
                "title" => $this->__("Message"),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_followup_edit_tab_message")->toHtml(),
            ));
        }

        return parent::_beforeToHtml();
    }

}
