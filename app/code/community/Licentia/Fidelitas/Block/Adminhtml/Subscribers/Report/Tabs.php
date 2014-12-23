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


class Licentia_Fidelitas_Block_Adminhtml_Subscribers_Report_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId("fidelitas_tabs");
        $this->setDestElementId("edit_form");
        $this->setTitle($this->__("Report Information"));
    }

    protected function _beforeToHtml() {
        $this->addTab("general", array(
            "label" => $this->__("General"),
            "title" => $this->__("General"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_reports_detail_general")->toHtml(),
        ));
        $this->addTab("location", array(
            "label" => $this->__("Location"),
            "title" => $this->__("Location"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_reports_detail_location")->toHtml(),
        ));
        $this->addTab("applications", array(
            "label" => $this->__("Applications"),
            "title" => $this->__("Applications"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_reports_detail_applications")->toHtml(),
        ));
        $this->addTab("domains", array(
            "label" => $this->__("Domains"),
            "title" => $this->__("Domains"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_reports_detail_domains")->toHtml(),
        ));
        $this->addTab("social", array(
            "label" => $this->__("Social"),
            "title" => $this->__("Social"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_reports_detail_social")->toHtml(),
        ));
        return parent::_beforeToHtml();
    }

}
