<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_Post extends Magpleasure_Blog_Block_Rss_Abstract
{
    public function getRssTitle()
    {
        return $this->_helper()->checkForPrefix($this->_helper()->__("Post Feed"));
    }

    public function getDataCollection()
    {
        $posts = array();

        /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $collection  */
        $collection = Mage::getModel('mpblog/post')->getCollection();

        if (!Mage::app()->isSingleStoreMode()){
            $collection->addStoreFilter($this->getStoreId());
        }

        $collection
            ->setDateOrder()
            ->setPageSize(10)
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED)
            ;

        foreach ($collection as $post){
            $posts[] = array(
                'title'         => $post->getTitle(),
                'link'          => $post->getPostUrl(),
                'description'   => $post->getFullContent(),
                'lastUpdate' 	=> strtotime($post->getUpdatedAt()),
            );
        }

        return $posts;
    }


}