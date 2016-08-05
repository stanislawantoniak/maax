<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Archive_Custom extends Magpleasure_Blog_Block_Sidebar_Archive
    implements Mage_Widget_Block_Interface
{
    protected $_customCollection;

    public function getDisplay()
    {
        return true;
    }

    protected function _getCacheParams()
    {
        $this->_keysToCache = array(
            'label',
            'record_limit',
        );

        $params = parent::_getCacheParams();
        $params[] = 'archive_custom';

        return $params;
    }

    public function getLimit()
    {
        return $this->getData('record_limit');
    }

    public function getBlockHeader()
    {
        return $this->getData('label');
    }
}