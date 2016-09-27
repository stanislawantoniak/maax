<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Recentcomments_Custom extends Magpleasure_Blog_Block_Sidebar_Recentcomments
    implements Mage_Widget_Block_Interface
{
    protected $_customCollection;



    protected function _getCacheParams()
    {
        $this->_keysToCache = array(
            'category_id',
            'label',
            'display_short',
            'display_date',
            'record_limit',
        );

        $dynamicCommentIds = $this->_helper()
            ->getCommon()
            ->getCookie()
            ->getAllFromCookie(
                $this->_helper()->getDynamicCookieName()
            );

        $params = parent::_getCacheParams();
        $params[] = 'recent_comments_custom';
        $params[] = count($dynamicCommentIds) ? implode("_", $dynamicCommentIds) : "NULL";

        return $params;
    }

    public function showThesis()
    {
        return $this->getData('display_short');
    }

    public function showDate()
    {
        return $this->getData('display_date');
    }

    public function getDisplay()
    {
        return true;
    }

    public function getCommentsLimit()
    {
        return $this->getData('record_limit');
    }

    public function getCategoryId()
    {
        if (($categoryId = $this->getData('category_id')) && ($categoryId !== '-')){
            return $categoryId;
        }
        return false;
    }

    public function getBlockHeader()
    {
        return $this->getData('label');
    }

    public function getCollection()
    {
        if (!$this->_customCollection){

            /** @var $collection Magpleasure_Blog_Model_Mysql4_Category_Collection */
            $collection = parent::getCollection();

            if ($categoryId = $this->getCategoryId()){
                $collection->addCategoryFilter($categoryId);
            }
            $this->_customCollection = $collection;
        }
        return $this->_customCollection;
    }
}