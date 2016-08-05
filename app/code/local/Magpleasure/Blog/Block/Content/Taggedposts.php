<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Taggedposts extends Magpleasure_Blog_Block_Sidebar_Taggedposts
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/custom.phtml");
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

        $ids = $this->_cachedIds;

        $params = parent::_getCacheParams();
        $params[] = 'content';

        return $params;
    }

    public function getReadMoreUrl($post)
    {
        return $this->_helper()->_url()->getUrl($post->getId());
    }
}