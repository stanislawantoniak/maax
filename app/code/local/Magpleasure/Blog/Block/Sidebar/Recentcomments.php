<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Recentcomments extends Magpleasure_Blog_Block_Sidebar_Abstract
{
    protected $_collection;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/sidebar/recentcomments.phtml");
        $this->_route = 'display_recent_comments';

        $cacheTags = $this->getCacheTags();
        $cacheTags[] = Magpleasure_Blog_Model_Comment::CACHE_TAG;
        $this->setCacheTags($cacheTags);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();

        $cacheKeyInfo['category_id'] = $this->getCategoryId();
        $cacheKeyInfo['display_short'] = $this->getDisplayShort();
        $cacheKeyInfo['record_limit'] = $this->getRecordLimit();
        $cacheKeyInfo['display_date'] = $this->getDisplayDate();

        $cacheKeyInfo['store_id'] = Mage::app()->getStore()->getId();

        return $cacheKeyInfo;
    }

    public function getCommentsLimit()
    {
        return $this->_helper()->getCommentsLimit();
    }

    public function getBlockHeader()
    {
        return $this->__('Recent Comments');
    }

    protected function _getCacheParams()
    {
        $dynamicCommentIds = $this->_helper()
                                    ->getCommon()
                                    ->getCookie()
                                    ->getAllFromCookie(
                                                    $this->_helper()->getDynamicCookieName()
                                                );

        $params = parent::_getCacheParams();
        $params[] = 'recent_comments';
        $params[] = count($dynamicCommentIds) ? implode("_", $dynamicCommentIds) : "NULL";

        return $params;
    }

    public function getCollection()
    {
        if (!$this->_collection){
            /** @var Magpleasure_Blog_Model_Mysql4_Comment_Collection  $collection  */
            $collection = Mage::getModel('mpblog/comment')->getCollection();
            if (!Mage::app()->isSingleStoreMode()){
                $collection->addPostStoreFilter(Mage::app()->getStore()->getId());
            }
            $collection
                ->addActiveFilter()
                ->setDateOrder()
                ;

            $collection->setPageSize($this->getCommentsLimit());
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function showThesis()
    {
        return $this->_helper()->getRecentCommentsDisplayShort();
    }

    public function showDate()
    {
        return $this->_helper()->getRecentCommentsDisplayDate();
    }

    public function renderDate($datetime)
    {
        return $this->_helper()->_date()->renderDate($datetime);
    }
}