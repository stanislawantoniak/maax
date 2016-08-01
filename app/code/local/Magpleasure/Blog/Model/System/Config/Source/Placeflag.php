<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_System_Config_Source_Placeflag
{	
    public function toOptionArray()
    {
        return array(
            array('value'=>'0', 'label'=>Mage::helper('mpblog')->__('No')),
            array('value'=>'left', 'label'=>Mage::helper('mpblog')->__('Left')),
            array('value'=>'right', 'label'=>Mage::helper('mpblog')->__('Right')),
        );
    }

}

