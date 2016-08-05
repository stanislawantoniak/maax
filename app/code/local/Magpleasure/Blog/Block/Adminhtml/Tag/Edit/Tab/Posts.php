<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Tag_Edit_Tab_Posts extends Mage_Adminhtml_Block_Widget_Container
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return $this->_helper()->__("Tagged Posts");
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->_helper()->__("Tagged Posts");
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    protected function _toHtml()
    {
        $grid = $this->getLayout()->createBlock('mpblog/adminhtml_tag_edit_tab_posts_grid');
        if ($grid){
            return $grid->toHtml();
        }
        return '';
    }
}