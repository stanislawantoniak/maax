<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_System_Config_Source_Postfix
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('mpblog')->__('No Postfix')),
            array('value'=>'.html', 'label'=>Mage::helper('mpblog')->__('.html')),
        );
    }
}

