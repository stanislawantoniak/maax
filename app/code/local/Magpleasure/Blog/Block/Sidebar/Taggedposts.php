<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Taggedposts extends Magpleasure_Blog_Block_Sidebar_Abstract
{
    protected $_collection;

    protected $_cachedIds;

    protected function _construct()
    {
        if ($transferedData = Mage::registry(Magpleasure_Blog_Block_Taggedposts::TRANSFER_KEY)){
            foreach ($transferedData as $key => $value){
                $this->setData($key, $value);
            }
        }

        parent::_construct();
        $this->setTemplate("mpblog/sidebar/recentpost.phtml");
        $this->_route = 'display_tagged';

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
        $this->_keysToCache = array(
            'block_title',
            'tags',
            'display_short',
            'record_limit',
            'display_date',
        );
        if (!$this->_cachedIds){
            Varien_Profiler::start('mpblog::cache::prepare_tagged_posts_ids');
            $clonedCollection = clone $this->getCollection();
            $this->_prepareCollectionToStart($clonedCollection, $this->getPostsLimit());
            $ids = $clonedCollection->getSelectedIds();
            $ids = count($ids) ? implode("_", $ids) : "NULL";
            $this->_cachedIds = $ids;
            Varien_Profiler::stop('mpblog::cache::prepare_tagged_posts_ids');
        }

        $ids = $this->_cachedIds;

        $params = parent::_getCacheParams();
        $params[] = 'tagged_posts';
        $params[] = $ids;


        return $params;
    }

    public function getPostsLimit()
    {
        return $this->getData('record_limit');
    }

    public function getTags()
    {
        return $this->getData('tags');
    }

    public function getBlockHeader()
    {
        return $this->getData('block_title');
    }

    public function getCollection()
    {
        if (!$this->_collection){
            /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection  $postCollection  */
            $postCollection = Mage::getModel('mpblog/post')->getCollection();




            if (!Mage::app()->isSingleStoreMode()){
                $postCollection->addStoreFilter(Mage::app()->getStore()->getId());
            }
            $postCollection->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);
            $postCollection->setUrlKeyIsNotNull();
            $postCollection->setDateOrder();

            //getting tag IDs of the tag names from widget settings
            $tagNames = explode(",", $this->getData('tags'));
            foreach ($tagNames as &$tagName){     //cutting whitespace from the beginning and end of tags
                $tagName = trim($tagName);
            }

            /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection $tagCollection */
            $tagCollection = Mage::getModel('mpblog/tag')->getCollection();
            $tagCollection->addTagNamesFilter($tagNames);
            $tagIds = $tagCollection->getAllIds();

            if (count($tagIds)){
                $postCollection->addTagsFilter($tagIds);
                $postCollection->setPageSize($this->getPostsLimit());
            } else {
                # Doesn't show anything if have no tags found
                $postCollection->setPageSize(0);
            }


            $this->_collection = $postCollection;
        }
        return $this->_collection;
    }

    public function getDisplay()
    {
        return true;
    }

    public function showThesis()
    {
        return $this->getData('display_short');
    }

    public function showDate()
    {
        return $this->getData('display_date');
    }

    public function renderDate($datetime)
    {
        return $this->_helper()->_date()->renderDate($datetime);
    }
}