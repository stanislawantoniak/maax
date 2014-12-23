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
class Licentia_Fidelitas_Block_Adminhtml_Subscribers_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = "source_id";
        $this->_blockGroup = "fidelitas";
        $this->_controller = "adminhtml_subscribers";

        $this->_addButton("saveandcontinuebarcode", array(
            "label" => $this->__("Save and Continue Edit"),
            "onclick" => "saveAndContinueEdit()",
            "class" => "save",
                ), -100);

        $this->_formScripts[] = " function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";

        $current = Mage::registry('current_subscriber');

        $list = Mage::getModel('fidelitas/lists')->load($current->getList(), 'listnum');

        if ($list->getId() && $list->getData('purpose') == 'client') {
           $this->_removeButton('save');
           $this->_removeButton('saveandcontinuebarcode');
           $this->_removeButton('reset');
        }
    }

    public function getHeaderText() {

        $current = Mage::registry("current_subscriber");

        if ($current && $current->getData()) {

            return $this->__("Edit Subscriber: " . $current->getData('last_name') . ', ' . $current->getData('first_name'));
        } else {
            return $this->__("Add Subscriber");
        }
    }

}
