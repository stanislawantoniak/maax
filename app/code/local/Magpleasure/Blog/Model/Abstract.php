<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Abstract extends Magpleasure_Common_Model_Abstract
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected $_storeId = null;

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        $this->setData('store_id', $storeId);
        return $this;
    }

    public function getStoreId()
    {
        return $this->hasData('store_id') ? $this->getData('store_id') : $this->_storeId;
    }
}