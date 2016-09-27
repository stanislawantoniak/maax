<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_Comment extends Magpleasure_Blog_Block_Rss_Abstract
{
    public function getRssTitle()
    {
        return $this->_helper()->checkForPrefix($this->_helper()->__("Comment Feed"));
    }

    public function getDataCollection()
    {
        $comments = array();

        /** @var Magpleasure_Blog_Model_Mysql4_Comment_Collection $collection  */
        $collection = Mage::getModel('mpblog/comment')->getCollection();

        if (!Mage::app()->isSingleStoreMode()){
            $collection->addStoreFilter($this->getStoreId());
        }

        if ($this->getPostId()){
            $collection->addPostFilter($this->getPostId());
        }

        $collection
            ->setDateOrder('DESC')
            ->setPageSize(10)
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Comment::STATUS_APPROVED)
            ;

        foreach ($collection as $comment){
            $comments[] = array(
                'title'         => $comment->getPost()->getTitle(),
                'link'          => $comment->getCommentUrl(),
                'description'   => $this->_helper()->_render()->render($comment->getMessage()),
                'lastUpdate' 	=> strtotime($comment->getUpdatedAt()),
            );
        }
        return $comments;
    }


}