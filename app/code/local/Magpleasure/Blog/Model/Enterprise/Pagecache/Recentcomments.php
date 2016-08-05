<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_Enterprise_Pagecache_Recentcomments extends Magpleasure_Blog_Model_Enterprise_Pagecache_Abstract
{
    /**
     * Cache tag prefix
     */
    const CACHE_TAG_PREFIX = 'mpblog_custom';

    /**
     * Get cache identifier
     *
     * @return string
     */
    protected function _getCacheId()
    {
        $cacheIdParts = array(self::CACHE_TAG_PREFIX);

        $cacheIdParts[] = $this->_placeholder->getAttribute('category_id');
        $cacheIdParts[] = $this->_placeholder->getAttribute('display_short') ? '1' : '0';
        $cacheIdParts[] = $this->_placeholder->getAttribute('record_limit') ? '1' : '0';
        $cacheIdParts[] = $this->_placeholder->getAttribute('display_date') ? '1' : '0';
        $cacheIdParts[] = $this->_placeholder->getAttribute('store_id');

        $cacheId = implode("_", $cacheIdParts);

        return $cacheId;
    }

    /**
     * Get container individual additional cache id
     *
     * @return string
     */
    protected function _getAdditionalCacheId()
    {
        return md5($this->_placeholder->getName() . '_' . $this->_placeholder->getAttribute('cache_id'));
    }

    /**
     * Render block content
     *
     * @return string
     */
    protected function _renderBlock()
    {
        /** @var $block Magpleasure_Ajaxbookmarks_Block_Widget */
        $block = $this->_getSafePlaceHolderBlock();

        $block->addData(array(
            'category_id' => $this->_placeholder->getAttribute('category_id'),
            'display_short' => $this->_placeholder->getAttribute('display_short'),
            'record_limit' => $this->_placeholder->getAttribute('record_limit'),
            'display_date' => $this->_placeholder->getAttribute('display_date'),
        ));

        Mage::dispatchEvent('render_block', array('block' => $block, 'placeholder' => $this->_placeholder));
        return $block->toHtml();
    }


}