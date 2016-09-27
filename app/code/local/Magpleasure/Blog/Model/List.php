<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_List extends Mage_Core_Model_Abstract implements Magpleasure_Blog_Model_Interface
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Blog URL
     *
     * @param array $params
     * @param int $page
     * @return string
     */
    public function getUrl($params = array(), $page = 1)
    {
        return $this->_helper()->_url()->getUrl(null, null, $page);
    }
}