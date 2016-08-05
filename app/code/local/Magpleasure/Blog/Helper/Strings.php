<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Strings extends Mage_Core_Helper_Abstract
{
    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Cut long text
     *
     * @param string $content
     * @param integer $limit
     * @return string
     */
    public function strLimit($content, $limit)
    {
        return $this->_helper()->getCommon()->getStrings()->strLimit($content, $limit);
    }

    /**
     * HTML to text
     *
     * @param string $content
     * @return string
     */
    public function htmlToText($content)
    {
        return $this->_helper()->stripTags($content);
    }
}