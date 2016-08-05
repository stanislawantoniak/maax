<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_Mysql4_Draft_Collection extends Magpleasure_Common_Model_Resource_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('mpblog/draft');
    }
}