<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_Enterprise_Pagecache_Abstract extends Enterprise_PageCache_Model_Container_Abstract
{
    /**
     * Get Place Holder Block
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _getSafePlaceHolderBlock()
    {
        if (method_exists($this, '_getPlaceHolderBlock')){
            return $this->_getPlaceHolderBlock();
        } else {
            $blockName = $this->_placeholder->getAttribute('block');
            $block = new $blockName;
            $block->setTemplate($this->_placeholder->getAttribute('template'));
            $block->setLayout(Mage::app()->getLayout());
            return $block;
        }
    }
}