<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

/** Social Networks */
class Magpleasure_Blog_Model_System_Config_Source_Networks
{	
    public function toOptionArray()
    {
        /** @var Magpleasure_Blog_Model_Networks $networks  */
        $networks = Mage::getModel('mpblog/networks');
        return $networks->toOptionArray();
    }	
	
}

