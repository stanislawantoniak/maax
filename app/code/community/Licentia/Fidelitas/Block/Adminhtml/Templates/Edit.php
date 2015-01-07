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
class Licentia_Fidelitas_Block_Adminhtml_Templates_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {
        parent::__construct();
        $this->_objectId = "template_id";
        $this->_blockGroup = "fidelitas";
        $this->_controller = "adminhtml_templates";
        $this->_updateButton('delete', 'onclick', 'deleteConfirm(\'' . Mage::helper('adminhtml')->__('Are you sure you want to do this?')
                . '\', \'' . $this->getUrl('*/*/delete/nid/' . $this->getRequest()->getParam('nid') . '/id/' . $this->_objectId) . '\')');

        $this->_addButton("saveandcontinuebarcode", array(
            "label" => $this->__("Save and Continue Edit"),
            "onclick" => "saveAndContinueEdit()",
            "class" => "save",
                ), -100);

        $this->_formScripts[] = " function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }";
    }

    public function getHeaderText() {

        if (Mage::registry("current_template") && Mage::registry("current_template")->getId()) {

            return $this->__("Edit Template");
        } else {

            return $this->__("Add Template");
        }
    }

    protected function _prepareLayout() {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

}
