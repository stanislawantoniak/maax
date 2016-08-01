<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Tag extends Magpleasure_Blog_Model_Abstract implements Magpleasure_Blog_Model_Interface
{

    public function _construct()
    {
        parent::_construct();
        $this->_init('mpblog/tag');
    }

    /**
     * Link Tag with Post
     *
     * @param $postId
     * @return Magpleasure_Blog_Model_Tag
     */
    public function linkWith($postId)
    {
        $this->getResource()->linkWith($this, $postId);
        return $this;
    }

    /**
     * Unlink Tag with Post
     *
     * @param $postId
     * @return Magpleasure_Blog_Model_Tag
     */
    public function unlinkWith($postId)
    {
        $this->getResource()->unlinkWith($this, $postId);
        return $this;
    }

    public function getTagUrl($page = 1)
    {
        return $this->_helper()->_url($this->getStoreId())->getUrl($this->getId(), Magpleasure_Blog_Helper_Url::ROUTE_TAG, $page);
    }

    public function getUrl($params = array(), $page = 1)
    {
        return $this->getTagUrl($page);
    }
}