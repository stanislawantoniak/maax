<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_Wrapper extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mpblog/rss/wrapper.phtml');
    }

    public function isBlogPage()
    {
        return ('mpblog' == $this->getRequest()->getModuleName());
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getPostFeedUrl()
    {
        $params = array();
        if (!Mage::app()->isSingleStoreMode()){
            $params['store_id'] = Mage::app()->getStore()->getId();
        }
        return $this->getUrl('mpblog/rss/post', $params);
    }

    public function getCommentFeedUrl()
    {
        $params = array();
        if (!Mage::app()->isSingleStoreMode()){
            $params['store_id'] = Mage::app()->getStore()->getId();
        }
        return $this->getUrl('mpblog/rss/comment', $params);
    }

    public function getCategoryFeedUrl()
    {
        $params = array('id'=>$this->getRequest()->getParam('id'));
        if (!Mage::app()->isSingleStoreMode()){
            $params['store_id'] = Mage::app()->getStore()->getId();
        }
        return $this->getUrl('mpblog/rss/category', $params);
    }

    public function checkForPrefix($title)
    {
        return $this->_helper()->checkForPrefix($title);
    }

    public function getCategoryName()
    {
        if ($this->isCategoryPage()){
            /** @var Magpleasure_Blog_Model_Category $category */
            $category = Mage::getModel('mpblog/category');
            if ($categoryId = $this->getRequest()->getParam('id')){
                $category->load($categoryId);

                if ($category->getId()){
                    return $category->getName();
                }
            }
        }
        return false;
    }

    public function isCategoryPage()
    {
        return $this->getRequest()->getActionName() == 'category';
    }
}