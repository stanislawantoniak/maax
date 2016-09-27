<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_System_Config_Source_Colors
{	
    public function toOptionArray()
    {
        return array(
            array('value'=>'mpblog-classic', 'label'=>Mage::helper('mpblog')->__('Classic')),
            array('value'=>'mpblog-red', 'label'=>Mage::helper('mpblog')->__('Red')),
            array('value'=>'mpblog-green', 'label'=>Mage::helper('mpblog')->__('Green')),
            array('value'=>'mpblog-blue', 'label'=>Mage::helper('mpblog')->__('Blue')),
            array('value'=>'mpblog-grey', 'label'=>Mage::helper('mpblog')->__('Grey')),
            array('value'=>'mpblog-old-magento', 'label'=>Mage::helper('mpblog')->__('Old Magento')),
        );
    }
}

