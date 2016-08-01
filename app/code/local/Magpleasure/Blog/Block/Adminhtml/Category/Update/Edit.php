<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Category_Update_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    protected $_posts = array();

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
        $this->_controller = 'adminhtml_category_update';

        $this->_updateButton('save', 'label', $this->_helper()->__('Update'));
        $this->_removeButton('delete');
        $this->_removeButton('reset');

        $this->_formScripts[] = "
        var checkboxChanged = function(id){
            $(id.replace('_view', '')).disabled = !$(id).checked;
        };
        ";
    }

    public function getHeaderText()
    {
        return $this->_helper()->__('Update Store View');
    }

}