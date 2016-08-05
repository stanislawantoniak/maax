<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_List extends Mage_Rss_Block_List
{
    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _getCategoryCollection()
    {
        /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $collection */
        $collection = Mage::getModel('mpblog/category')->getCollection();

        $collection
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Category::STATUS_ENABLED)
            ->setSortOrder('asc')
            ;

        if (!Mage::app()->isSingleStoreMode()){
            $collection->addStoreFilter(Mage::app()->getStore()->getId());
        }

        return $collection;
    }

    public function addMpBlogFeeds()
    {
        $prefix = $this->_helper()->getMenuLabel();
        $postFeedTitle = $this->_helper()->__("%s - Post Feed", $prefix);
        $commentFeedTitle = $this->_helper()->__("%s - Comment Feed", $prefix);
        if ($this->_helper()->getRssPost()){
            $this->addRssFeed('mpblog/rss/post', $postFeedTitle);
        }

        if ($this->_helper()->getRssCatgeory()){
            foreach ($this->_getCategoryCollection() as $category){
                $categoryFeedTitle = $this->_helper()->__("%s - %s Feed", $prefix, $category->getName());
                $this->addRssFeed('mpblog/rss/category', $categoryFeedTitle, array('id' => $category->getId()));
            }
        }

        if ($this->_helper()->getRssComment()){
            $this->addRssFeed('mpblog/rss/comment', $commentFeedTitle);
        }
        return $this;
    }

    public function getRssMiscFeeds()
    {
        parent::getRssMiscFeeds();
        $this->addMpBlogFeeds();
        Mage::dispatchEvent('core_rss_feed_generate', array('rss_list'=>$this));
        return $this->getRssFeeds();

    }


}