<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_Tag extends Magpleasure_Blog_Block_Rss_Abstract
{
    protected function _tagName()
    {
        /** @var Magpleasure_Blog_Model_Tag $tag */
        $tag = Mage::getModel('mpblog/tag');
        if ($tagId = $this->getRequest()->getParam('id')) {
            $tag->load($tagId);

            if ($tag->getId()) {
                return $tag->getName();
            }
        }

        return false;
    }

    public function getRssTitle()
    {
        $tagName = $this->_tagName();
        if ($tagName){
            return $this->_helper()->checkForPrefix($this->_helper()->__("'%s' Tag Feed", $tagName));
        } else {
            return $this->_helper()->checkForPrefix($this->_helper()->__("Tag Feed"));
        }
    }

    public function getDataCollection()
    {
        $tags = array();

        /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $collection */
        $collection = Mage::getModel('mpblog/post')->getCollection();

        if ($id = $this->getRequest()->getParam('id')) {
            $collection->addTagFilter($id);
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $collection->addStoreFilter($this->getStoreId());
        }

        $collection
            ->setDateOrder()
            ->setPageSize(10)
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);

        foreach ($collection as $tag) {
            $tags[] = array(
                'title' => $tag->getTitle(),
                'link' => $tag->getPostUrl(),
                'description' => $tag->getFullContent(),
                'lastUpdate' => strtotime($tag->getUpdatedAt()),
            );
        }

        return $tags;
    }
}