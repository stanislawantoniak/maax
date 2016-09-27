<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Category_Custom extends Magpleasure_Blog_Block_Sidebar_Category
    implements Mage_Widget_Block_Interface
{
    protected $_customCollection;


    protected function _getCacheParams()
    {
        $this->_keysToCache = array(
            'label',
        );

        $params = parent::_getCacheParams();
        $params[] = 'category_custom';

        return $params;
    }

    public function getDisplay()
    {
        return true;
    }

    public function getBlockHeader()
    {
        return $this->getData('label');
    }
}