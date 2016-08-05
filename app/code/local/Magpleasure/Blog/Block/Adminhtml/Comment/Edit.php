<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Comment_Edit extends Magpleasure_Blog_Block_Adminhtml_Filterable_Edit
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
        $this->_controller = 'adminhtml_comment';

        $this->_updateButton('save', 'label', $this->_helper()->__('Save Comment'));
        $this->_updateButton('delete', 'label', $this->_helper()->__('Delete Comment'));

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

        if (!!Mage::registry('current_comment')){
            $this->_removeButton('delete');
        }
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_comment') && Mage::registry('current_comment')->getId()) {
            return $this->_helper()->__("Edit Comment of '%s'", $this->escapeHtml(Mage::registry('current_comment')->getName()));
        } elseif (Mage::registry('comment_for_answer') && Mage::registry('comment_for_answer')->getId()) {
            return $this->_helper()->__("Answer for '%s'", $this->escapeHtml(Mage::registry('comment_for_answer')->getName()));
        }
        return false;
    }
}