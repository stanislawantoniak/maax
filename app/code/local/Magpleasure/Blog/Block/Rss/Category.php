<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_Category extends Magpleasure_Blog_Block_Rss_Abstract
{
    protected function _categoryName()
    {
        /** @var Magpleasure_Blog_Model_Category $category */
        $category = Mage::getModel('mpblog/category');
        if ($categoryId = $this->getRequest()->getParam('id')) {
            $category->load($categoryId);

            if ($category->getId()) {
                return $category->getName();
            }
        }

        return false;
    }

    public function getRssTitle()
    {
        $categoryName = $this->_categoryName();
        if ($categoryName){
            return $this->_helper()->checkForPrefix($this->_helper()->__("%s Feed", $categoryName));
        } else {
            return $this->_helper()->checkForPrefix($this->_helper()->__("Category Feed"));
        }
    }

    public function getDataCollection()
    {
        $posts = array();

        /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $collection */
        $collection = Mage::getModel('mpblog/post')->getCollection();

        if ($categoryId = $this->getRequest()->getParam('id')) {
            $collection->addCategoryFilter($categoryId);
        }

        if (!Mage::app()->isSingleStoreMode()) {
            $collection->addStoreFilter($this->getStoreId());
        }

        $collection
            ->setDateOrder()
            ->setPageSize(10)
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Post::STATUS_ENABLED);

        foreach ($collection as $post) {
            $posts[] = array(
                'title' => $post->getTitle(),
                'link' => $post->getPostUrl(),
                'description' => $post->getFullContent(),
                'lastUpdate' => strtotime($post->getUpdatedAt()),
            );
        }

        return $posts;
    }
}