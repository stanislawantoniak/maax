<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Searchcore
 */

class Magpleasure_Searchcore_Model_Resource_Type extends Magpleasure_Common_Model_Resource_Abstract
{
    protected function _construct()
    {    
        $this->_init('searchcore/type', 'type_id');
    }
}