<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class  Magpleasure_Blog_Block_Adminhtml_Widget_Form_Views_Renderer
    extends Magpleasure_Common_Block_Adminhtml_Template
{
    protected $_post;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mpblog/system/post/views.phtml');
    }

    /**
     * Post
     *
     * @return Magpleasure_Blog_Model_Post
     */
    public function getPost()
    {
        return Mage::registry('current_post');
    }

    public function getViewsCount()
    {
        return $this->getPost()->getViews();
    }
}