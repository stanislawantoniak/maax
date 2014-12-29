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


class Licentia_Fidelitas_Block_Adminhtml_Splits_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("fidelitas_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle($this->__("A/B Campaign"));
    }

    protected function _beforeToHtml() {
        $this->addTab("form_section", array(
            "label" => $this->__("General"),
            "title" => $this->__("General"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_splits_edit_tab_form")->toHtml(),
        ));
        $this->addTab("emaila_section", array(
            "label" => $this->__("Email A"),
            "title" => $this->__("Email A"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_splits_edit_tab_emaila")->toHtml(),
        ));
        $this->addTab("emailb_section", array(
            "label" => $this->__("Email B"),
            "title" => $this->__("Email B"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_splits_edit_tab_emailb")->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
