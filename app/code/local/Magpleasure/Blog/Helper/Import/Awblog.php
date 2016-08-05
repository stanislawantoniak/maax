<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Import_Awblog
    extends Magpleasure_Blog_Helper_Import_Abstract
{
    protected $_awToMpCatgory = array();
    protected $_awToMpPost = array();

    protected $_categoryMask = array(
        'title' => array('name', 'meta_title'),
        'sort_order' => 'sort_order',
        'store_id' => 'stores',
        'identifier' => 'url_key',
        'meta_keywords' => 'meta_tags',
        'meta_description' => 'meta_description',
    );

    protected $_postMask = array(
        'title' => array('title', 'meta_title'),
        'post_content' => 'full_content',
        'identifier' => 'url_key',
        'user' => 'posted_by',
        'meta_keywords' => 'meta_tags',
        'meta_description' => 'meta_description',
        'comments' => 'use_comments',
        'tags' => 'tags',
        'short_content' => 'short_content',
        'store_id' => 'stores',
        'created_time' => 'created_at',
        'updated_time' => 'updated_at',
    );

    protected $_commentMask = array(
        'created_time' => 'created_at',
        'user' => 'name',
        'email' => 'email',
    );

    protected $_postStatusConvert = array(
        1 => Magpleasure_Blog_Model_Post::STATUS_ENABLED,
        2 => Magpleasure_Blog_Model_Post::STATUS_DISABLED,
        3 => Magpleasure_Blog_Model_Post::STATUS_HIDDEN,
    );

    protected $_commentStatusConvert = array(
        1 => Magpleasure_Blog_Model_Comment::STATUS_REJECTED,
        2 => Magpleasure_Blog_Model_Comment::STATUS_APPROVED,
    );

    public function isBlogInstalled()
    {
        return $this->isModuleEnabled("AW_Blog");
    }

    public function import($verbose = false, $data = array())
    {
        parent::import($verbose, $data);

        if (!$this->isBlogInstalled()){
            Mage::throwException("aheadWorks Blog is not installed or disabled.");
        }

        $this->importCategories($verbose);
        $this->importPosts($verbose);
        $this->importComments($verbose);

        return $this;
    }

    public function importCategories($verbose = false)
    {
        if ($verbose){
            echo "- Import Categories \n";
        }

        /** @var AW_Blog_Model_Mysql4_Cat_Collection $awCategories  */
        $awCategories = Mage::getModel('blog/cat')->getCollection();
        $awCategories->setOrder('cat_id');
        foreach ($awCategories as $awCategory){
            $awCategory = Mage::getModel('blog/cat')->load($awCategory->getId());
            $mpCategory = Mage::getModel('mpblog/category');
            $mpCategory
                ->addData($this->_prepareData($this->_categoryMask, $awCategory))
                ->setStatus(Magpleasure_Blog_Model_Category::STATUS_ENABLED)
                ->save()
                ;

            $this->_awToMpCatgory[$awCategory->getId()] = $mpCategory->getId();
        }
        return $this;
    }

    protected function _getCategories(array $awCategories)
    {
        $result = array();
        foreach ($awCategories as $awCategoryId){
            if (isset($this->_awToMpCatgory[$awCategoryId])){
                $result[] = $this->_awToMpCatgory[$awCategoryId];
            }
        }
        return $result;
    }


    protected function _getCommentStatus($awStatus)
    {
        if (isset($this->_commentStatusConvert[$awStatus])){
            return $this->_commentStatusConvert[$awStatus];
        }
        return false;
    }


    protected function _getPostStatus($awStatus)
    {
        if (isset($this->_postStatusConvert[$awStatus])){
            return $this->_postStatusConvert[$awStatus];
        }
        return false;
    }

    public function importPosts($verbose = false)
    {
        if ($verbose){
            echo "- Import Posts \n";
        }

        /** @var AW_Blog_Model_Mysql4_Cat_Collection $awPosts  */
        $awPosts = Mage::getModel('blog/post')->getCollection();
        $awPosts->setOrder('post_id');

        foreach ($awPosts as $awPost){
            $awPost = Mage::getModel('blog/post')->load($awPost->getId());
            $mpPost = Mage::getModel('mpblog/post');
            $mpPost
                ->addData($this->_prepareData($this->_postMask, $awPost))
                ->setStatus($this->_getPostStatus($awPost->getStatus()))
                ->setCategories($this->_getCategories($awPost->getCatId()))
                ->save()
            ;

            $mpPost
                ->setCreatedAt($awPost->getCreatedTime())
                ->setPublishedAt($awPost->getCreatedTime())
                ->save()
                ;


            $this->_awToMpPost[$awPost->getId()] = $mpPost->getId();
        }
        return $this;

    }

    protected function _prepareComment($message)
    {
        $message = html_entity_decode($message);
        return strip_tags($message);
    }


    protected function _getCustomerId($email, $storeId = null)
    {
        /** @var Mage_Customer_Model_Customer $customer  */
        $customer = Mage::getModel('customer/customer');
        $customer
            ->setStore(Mage::app()->getStore($storeId))
            ->loadByEmail($email)
        ;

        if ($customer->getId()){
            return $customer->getId();
        }
        return null;
    }

    public function importComments($verbose = false)
    {
        if ($verbose){
            echo "- Import Comments \n";
        }

        /** @var AW_Blog_Model_Mysql4_Comment_Collection $awComments  */
        $awComments = Mage::getModel('blog/comment')->getCollection();
        $awComments->setOrder('comment_id');

        foreach ($awComments as $awComment){
            $awComment = Mage::getModel('blog/comment')->load($awComment->getId());
            $awPost = Mage::getModel('blog/post')->load($awComment->getPostId());
            if ($awPost->getId()){
                $stores = $awPost->getStoreId();
                $storeId = count($stores) ? $stores[0] : Mage::app()->getAnyStoreView()->getId();
                $mpComment = Mage::getModel('mpblog/comment');
                $mpComment
                    ->addData($this->_prepareData($this->_commentMask, $awComment))
                    ->setStatus($this->_getCommentStatus($awComment->getStatus()))
                    ->setMessage($this->_prepareComment($awComment->getComment()))
                    ->setCustomerId($this->_getCustomerId($awComment->getEmail(), $storeId))
                    ->setPostId($this->_awToMpPost[$awComment->getPostId()])
                    ->setStoreId($storeId)
                    ->save()
                ;

                $mpComment
                    ->setCreatedAt($awComment->getCreatedTime())
                    ->save()
                ;

            }
        }
        return $this;
    }
}