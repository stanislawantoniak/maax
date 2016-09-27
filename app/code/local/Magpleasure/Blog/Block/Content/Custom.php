<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Custom extends Magpleasure_Blog_Block_Sidebar_Custom
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/custom.phtml");
    }

    protected function _getCacheParams()
    {
        $this->_keysToCache = array(
            'category_id',
            'display_short',
            'record_limit',
            'display_date',
        );

        $params = parent::_getCacheParams();
        $params[] = 'content';

        return $params;
    }

    public function getReadMoreUrl($post)
    {
        return $this->_helper()->_url()->getUrl($post->getId());
    }
}