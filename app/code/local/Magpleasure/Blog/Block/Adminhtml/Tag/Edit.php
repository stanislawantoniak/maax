<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Tag_Edit extends Magpleasure_Blog_Block_Adminhtml_Filterable_Edit
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mpblog';
        $this->_controller = 'adminhtml_tag';

        $this->_updateButton('save', 'label', $this->_helper()->__('Save Tag'));
        $this->_updateButton('delete', 'label', $this->_helper()->__('Delete Tag'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_tag') && Mage::registry('current_tag')->getId()) {
            return $this->_helper()->__("Edit Tag '%s'", $this->escapeHtml(Mage::registry('current_tag')->getName()));
        }
        return false;
    }
}