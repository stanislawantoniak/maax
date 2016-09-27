<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Post_Details extends Mage_Core_Block_Template
{
    protected $_post;

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Set current post
     *
     * @param Magpleasure_Blog_Model_Post $post
     * @return Magpleasure_Blog_Block_Content_List_Details
     */
    public function setPost(Magpleasure_Blog_Model_Post $post)
    {
        $this->_post = $post;
        return $this;
    }

    /**
     * Post
     *
     * @return Magpleasure_Blog_Model_Post|null
     */
    public function getPost()
    {
        return $this->_post;
    }

    public function isPost()
    {
        return ($this->getRequest()->getActionName() == 'post');
    }

    public function renderDate($datetime)
    {
        return $this->_helper()->_date()->renderDate($datetime);
    }

    public function getLeaveCommentUrl()
    {
        return $this->getPost()->getPostUrl()."#form";
    }

    public function getCommentsUrl()
    {
        return $this->getPost()->getPostUrl()."#comments";
    }

    public function getCommentsCount()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Comment_Collection $comments  */
        $comments = Mage::getModel('mpblog/comment')->getCollection();

        if (!Mage::app()->isSingleStoreMode()){
            $comments->addStoreFilter(Mage::app()->getStore()->getId());
        }

        $comments
            ->addPostFilter($this->getPost()->getId())
            ->addActiveFilter()
            ;
        return $comments->getSize();
    }

    public function getTagsHtml()
    {
        $tagDetails = $this->getLayout()->createBlock('mpblog/content_post_details');
        if ($tagDetails){
            $tagDetails
                ->setPost($this->getPost())
                ->setTemplate('mpblog/list/tags.phtml');
            ;
            return $tagDetails->toHtml();
        }
        return false;
    }

    public function getCategoriesHtml()
    {
        $catDetails = $this->getLayout()->createBlock('mpblog/content_post_details');
        if ($catDetails){
            $catDetails
                ->setPost($this->getPost())
                ->setTemplate('mpblog/list/categories.phtml');
                ;
            return $catDetails->toHtml();
        }
        return false;
    }

    /**
     * Tags
     *
     * @return Magpleasure_Blog_Model_Mysql4_Tag_Collection
     */
    public function getTags()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection $tags  */
        $tags = Mage::getModel('mpblog/tag')->getCollection();
        $tags->addPostFilter($this->getPost()->getId());

        return $tags;
    }

    /**
     * Categories
     *
     * @return Magpleasure_Blog_Model_Mysql4_Category_Collection
     */
    public function getCategories()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $categories  */
        $categories = Mage::getModel('mpblog/category')->getCollection();
        $categories
            ->addPostFilter($this->getPost()->getId())
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Category::STATUS_ENABLED)
        ;

        if (!Mage::app()->isSingleStoreMode()){
            $categories->addStoreFilter(Mage::app()->getStore()->getId());
        }
        return $categories;
    }

    public function showAuthor()
    {
        return $this->_helper()->getShowAuthor();
    }

    public function useGoogleProfile()
    {
        return !!$this->getPost()->getPostedBy() && !!$this->getPost()->getGoogleProfile();
    }

    public function getGoogleProfileUrl()
    {
        return $this->getPost()->getGoogleProfile();
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isOldStyle()
    {
        return false;
    }

    public function getColorClass()
    {
        return $this->_helper()->getIconColorClass();
    }
}