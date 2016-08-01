<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Model_System_Config_Source_Categories
{	
    public function toOptionArray()
    {
        $result = array(
            array('value'=>'-', 'label'=>Mage::helper('mpblog')->__('All Categories')),
        );


        /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $categories  */
        $categories = Mage::getModel('mpblog/category')->getCollection();
        $categories
            ->setSortOrder('asc')
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Category::STATUS_ENABLED)
            ;

        foreach ($categories as $category){
            $result[] = array('value'=>$category->getId(), 'label'=>$category->getName());
        }

        return $result;
    }	
	
}

