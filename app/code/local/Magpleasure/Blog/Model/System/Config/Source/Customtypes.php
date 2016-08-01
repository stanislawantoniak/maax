<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_System_Config_Source_Customtypes
{	
    public function toOptionArray()
    {
        return array(
            array('value'=>'sidebar', 'label'=>Mage::helper('mpblog')->__('Sidebar')),
            array('value'=>'content', 'label'=>Mage::helper('mpblog')->__('Content')),
        );
    }	
	
}

