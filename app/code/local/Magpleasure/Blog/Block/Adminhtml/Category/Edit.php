<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Category_Edit extends Magpleasure_Blog_Block_Adminhtml_Filterable_Edit
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getId()
    {
        if ($id = $this->getRequest()->getParam('id')){
            return $id;
        } else {
            return false;
        }
    }

    public function __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'mpblog';
        $this->_controller = 'adminhtml_category';

        $this->_updateButton('save', 'label', $this->_helper()->__('Save Category'));
        $this->_updateButton('delete', 'label', $this->_helper()->__('Delete Category'));

        $this->_addButton('saveandcontinue', array(
            'label' => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick' => 'saveAndContinueEdit()',
            'class' => 'save',
        ), -100);

        if ($this->_getId()){
            $this->addButton('duplicate', array(
                'title' => $this->_helper()->__("Duplicate"),
                'label' => $this->_helper()->__("Duplicate"),
                'onclick' => "duplicate();",
                'class' => 'scalable save duplicate',
            ), 1, 2);

            $params = $this->_getCommonParams();
            $params['id'] = $this->_getId();

            $duplicateUrl = $this->getUrl('*/*/duplicate', $params);
            $confirmationMessage = $this->_helper()->__("Please confirm duplicating. All data that hasn't been saved will be lost.");
            $confirmationMessage = str_replace("'", "\\'",$confirmationMessage);
            $this->_formScripts[] = "
                function duplicate(){
                    if (confirm('{$confirmationMessage}')){
                        window.location = '{$duplicateUrl}';
                    }
                }
            ";
        }

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('current_category') && Mage::registry('current_category')->getId()) {
            return $this->_helper()->__("Edit Category '%s'", $this->escapeHtml(Mage::registry('current_category')->getName()));
        } else {
            return $this->_helper()->__('New Category');
        }
    }
}