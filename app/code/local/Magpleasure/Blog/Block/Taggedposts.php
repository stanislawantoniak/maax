<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Taggedposts extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    const TRANSFER_KEY = 'MP_BLOG_TAGGEDPOSTS_WIDGET_TRANSFER_DATA';

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {

        $cacheKeyInfo = parent::getCacheKeyInfo();

        $cacheKeyInfo['block_type'] = $this->getBlockType();
        $cacheKeyInfo['block_title'] = $this->getBlockTitle();
        $cacheKeyInfo['tags'] = $this->getTags();
        $cacheKeyInfo['display_short'] = $this->getDisplayShort();
        $cacheKeyInfo['record_limit'] = $this->getRecordLimit();
        $cacheKeyInfo['display_date'] = $this->getDisplayDate();
        $cacheKeyInfo['store_id'] = Mage::app()->getStore()->getId();

        return $cacheKeyInfo;
    }

    protected $_dataToTransfer = array(
                                    'block_title',
                                    'tags',
                                    'display_short',
                                    'record_limit',
                                    'display_date',
                                );

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _toHtml()
    {
        if ($blockType = $this->getBlockType()){
            $transfer = array();
            foreach ($this->_dataToTransfer as $key){
                $transfer[$key] = $this->getData($key);
            }
            Mage::register(self::TRANSFER_KEY, $transfer, true);
            $block = $this->getLayout()->createBlock("mpblog/{$blockType}_taggedposts");
            if ($block){

                return $block->toHtml();
            }
        }
        return  false;
    }

}