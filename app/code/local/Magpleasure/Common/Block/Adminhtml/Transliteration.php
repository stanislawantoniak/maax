<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Transliteration extends Magpleasure_Common_Block_Adminhtml_Template
{
    /**
     * Common Helper
     *
     * @return Magpleasure_Common_Helper_Data
     */
    public function _commonHelper()
    {
        return Mage::helper('magpleasure');
    }

    public function getSlugUrl()
    {
        return $this->getUrl('adminhtml/magpleasure_slug/generate');
    }
}