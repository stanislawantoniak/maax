<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Recentpost extends Magpleasure_Blog_Block_Sidebar_Abstract
{
    protected $_collection;

    protected $_cachedIds;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/sidebar/recentpost.phtml");
        $this->_route = 'display_recent';

        $cacheTags = $this->getCacheTags();
        $cacheTags[] = Magpleasure_Blog_Model_Post::CACHE_TAG;
        $this->setCacheTags($cacheTags);
    }

    protected function _getPostLimit()
    {
        return $this->_helper()->getRecentPostsLimit();
    }

    protected function _getCacheParams()
    {
        if (!$this->_cachedIds){
            Varien_Profiler::start('mpblog::cache::prepare_recent_posts_ids');
            $clonedCollection = clone $this->getCollection();
            $this->_prepareCollectionToStart($clonedCollection, $this->getPostsLimit());
            $ids = $clonedCollection->getSelectedIds();
            $ids = count($ids) ? implode("_", $ids) : "NULL";
            $this->_cachedIds = $ids;
            Varien_Profiler::stop('mpblog::cache::prepare_recent_posts_ids');
        }

        $ids = $this->_cachedIds;

        $params = parent::_getCacheParams();
        $params[] = 'recent_posts';
        $params[] = $ids;

        return $params;
    }

    public function getPostsLimit()
    {
        return $this->_helper()->getRecentPostsLimit();
    }

    public function getBlockHeader()
    {
        return $this->__('Recent Posts');
    }

    public function getCollection()
    {
        if (!$this->_collection){
            /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection  $collection  */
            $collection = Mage::getModel('mpblog/post')->getCollection();
            if (!Mage::app()->isSingleStoreMode()){
                $collection->addStoreFilter(Mage::app()->getStore()->getId());
            }
            $collection->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);
            $collection->setUrlKeyIsNotNull();
            $collection->setDateOrder();

            $this->_checkCategory($collection);
            $collection->setPageSize($this->getPostsLimit());

            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function showThesis()
    {
        return $this->_helper()->getRecentPostsDisplayShort();
    }

    public function showDate()
    {
        return $this->_helper()->getRecentPostsDisplayDate();
    }

    public function renderDate($datetime)
    {
        return $this->_helper()->_date()->renderDate($datetime);
    }

    public function hasThumbnail($post)
    {
        $src = $post->getListThumbnail() ? $post->getListThumbnail() : $post->getPostThumbnail();
        return !!$src;
    }

    protected function _getThumbnailSrc($src, $width, $height = null)
    {
        $imageHelper = $this->_helper()->getCommon()->getImage();
        $height = $height ? $height : $width;
        $imageHelper->init($src)->adaptiveResize($width, $height);
        return $imageHelper->__toString();
    }

    public function getThumbnailSrc($post)
    {
        $src = $post->getListThumbnail() ? $post->getListThumbnail() : $post->getPostThumbnail();
        if ($src){
            return $this->_getThumbnailSrc($src, 60);
        }

        return false;
    }
}