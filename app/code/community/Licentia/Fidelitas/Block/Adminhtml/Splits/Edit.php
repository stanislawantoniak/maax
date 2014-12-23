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
class Licentia_Fidelitas_Block_Adminhtml_Splits_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = "split_id";
        $this->_blockGroup = "fidelitas";
        $this->_controller = "adminhtml_splits";

        $this->_addButton("saveandcontinuebarcode", array(
            "label" => $this->__("Save and Continue Edit"),
            "onclick" => "saveAndContinueEdit()",
            "class" => "save",
                ), -100);

        $this->_formScripts[] = " function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";

        $current = Mage::registry('current_split');


        if ($current && $current->getClosed() == 1) {
            $this->_removeButton('add');
            $this->_removeButton('save');
            $this->_removeButton('saveandcontinuebarcode');
        }

        if ($current &&
                $current->getId() &&
                $current->getClosed() == 0 &&
                $current->getActive() == 1 &&
                $current->getWinner() == 'manually' &&
                $current->getSent() == 1) {

            $location = $this->getUrl('*/*/send', array('id' => $current->getId(), 'winner' => 'a'));

            $this->_addButton("send_a", array(
                "label" => $this->__("Send Test A"),
                "onclick" => "if(!confirm('Send Test A Now')){return false;}; window.location='$location'",
                "class" => "save",
                    ), -100);


            $location = $this->getUrl('*/*/send', array('id' => $current->getId(), 'winner' => 'b'));

            $this->_addButton("send_b", array(
                "label" => $this->__("Send Test B"),
                "onclick" => "if(!confirm('Send Test A Now')){return false;}; window.location='$location'",
                "class" => "save",
                    ), -100);
        }
    }

    public function getHeaderText() {

        if (Mage::registry("current_split") && Mage::registry("current_split")->getId()) {
            return $this->__("Edit A/B Campaign");
        } else {
            return $this->__("Add A/B Campaign");
        }
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

}

