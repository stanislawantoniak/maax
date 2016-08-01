<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Custom extends Mage_Core_Block_Template implements Mage_Widget_Block_Interface
{
    const TRANSFER_KEY = 'MP_BLOG_CUSTOM_WIDGET_TRANSFER_DATA';

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        $cacheKeyInfo = parent::getCacheKeyInfo();

        $cacheKeyInfo['block_type'] = $this->getBlockType();
        $cacheKeyInfo['category_id'] = $this->getCategoryId();
        $cacheKeyInfo['display_short'] = $this->getDisplayShort();
        $cacheKeyInfo['record_limit'] = $this->getRecordLimit();
        $cacheKeyInfo['display_date'] = $this->getDisplayDate();

        $cacheKeyInfo['store_id'] = Mage::app()->getStore()->getId();


        return $cacheKeyInfo;
    }

    protected $_dataToTransfer = array(
        'category_id',
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

            if (Mage::registry(self::TRANSFER_KEY)){
                Mage::unregister(self::TRANSFER_KEY);
            }

            Mage::register(self::TRANSFER_KEY, $transfer);

            $block = $this->getLayout()->createBlock("mpblog/{$blockType}_custom");
            if ($block){

                return $block->toHtml();
            }
        }
        return  false;
    }

}