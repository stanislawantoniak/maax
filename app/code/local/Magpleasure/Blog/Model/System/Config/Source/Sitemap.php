<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Model_System_Config_Source_Sitemap
    extends Magpleasure_Common_Model_System_Config_Source_Abstract
{
    public function toOptionArray()
    {
        return array(
            array('value'=>Magpleasure_Blog_Model_Sitemap::MPBLOG_TYPE_BLOG, 'label'=>Mage::helper('mpblog')->__('Blog')),
            array('value'=>Magpleasure_Blog_Model_Sitemap::MPBLOG_TYPE_POST, 'label'=>Mage::helper('mpblog')->__('Posts')),
            array('value'=>Magpleasure_Blog_Model_Sitemap::MPBLOG_TYPE_CATEGORY, 'label'=>Mage::helper('mpblog')->__('Categories')),
            array('value'=>Magpleasure_Blog_Model_Sitemap::MPBLOG_TYPE_TAG, 'label'=>Mage::helper('mpblog')->__('Tags')),
            array('value'=>Magpleasure_Blog_Model_Sitemap::MPBLOG_TYPE_ARCHIVE, 'label'=>Mage::helper('mpblog')->__('Archives')),
        );
    }
}

