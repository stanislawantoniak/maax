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
class Licentia_Fidelitas_Block_Adminhtml_Campaigns_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs {

    public function __construct() {
        parent::__construct();
        $this->setId('reports_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Campaigns'));
    }

    protected function _beforeToHtml() {

        $current = Mage::registry('current_campaign');
        $followup = Mage::registry('current_followup');
        $listnum = $this->getRequest()->getParam('listnum');
        if ($current->getId()) {
            $listnum = $current->getListnum();
        }


        $type = $this->getRequest()->getParam('type');

        if ($current->getChannel()) {
            $type = strtolower($current->getChannel());
        }

        if (!$listnum) {
            $this->addTab("form_section", array(
                "label" => $this->__("General"),
                "title" => $this->__("General"),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_" . $type)->toHtml(),
            ));

            return parent::_beforeToHtml();
        }


        $this->addTab("form_section", array(
            "label" => $this->__("General"),
            "title" => $this->__("General"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_" . $type)->toHtml(),
        ));


        $this->addTab("recurring_section", array(
            "label" => $this->__("Sending Options"),
            "title" => $this->__("Sending Options"),
            "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_recurring")->toHtml(),
        ));

        if (strtolower($type) == 'email') {

            $this->addTab("content_section", array(
                "label" => $this->__("Content"),
                "title" => $this->__("Content"),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_content")->toHtml(),
            ));

            $this->addTab("links_section", array(
                "label" => $this->__("Links"),
                "title" => $this->__("Links"),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_links")->toHtml(),
            ));

            $this->addTab("advanced_section", array(
                "label" => $this->__("Advanced"),
                "title" => $this->__("Advanced"),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_advanced")->toHtml(),
            ));
        }

        if ($current->getId() && $current->getRecurring() != '0') {

            $this->addTab('children', array(
                'label' => $this->__('Children Campaigns'),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_children_grid")->toHtml(),
            ));
        }

        if ($followup->count() > 0) {
            $this->addTab('followup', array(
                'label' => $this->__('Follow Up Queue'),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_followup")->toHtml(),
            ));
            $this->addTab('followup_sent', array(
                'label' => $this->__('Follow Up Sents'),
                "content" => $this->getLayout()->createBlock("fidelitas/adminhtml_campaigns_edit_tab_followsent")->toHtml(),
            ));
        }
        $this->_updateActiveTab();
        return parent::_beforeToHtml();
    }

    protected function _updateActiveTab() {
        $tabId = $this->getRequest()->getParam('tab');
        if ($tabId) {
            $tabId = preg_replace("#{$this->getId()}_#", '', $tabId);
            if ($tabId) {
                $this->setActiveTab($tabId);
            }
        }
    }

}
