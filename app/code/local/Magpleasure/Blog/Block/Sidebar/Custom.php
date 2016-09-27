<?php
    /**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Custom extends Magpleasure_Blog_Block_Sidebar_Recentpost
{
    protected function _construct()
    {
        if ($transferedData = Mage::registry(Magpleasure_Blog_Block_Custom::TRANSFER_KEY)){
            foreach ($transferedData as $key => $value){
                $this->setData($key, $value);
            }
        }

        parent::_construct();
    }

    protected function _getCacheParams()
    {
        $this->_keysToCache = array(
            'category_id',
            'display_short',
            'record_limit',
            'display_date',
        );

        $params = parent::_getCacheParams();
        $params[] = 'custom';

        return $params;
    }


    public function showThesis()
    {
        return $this->getData('display_short');
    }

    public function showDate()
    {
        return $this->getData('display_date');
    }

    public function getDisplay()
    {
        return true;
    }

    public function getPostsLimit()
    {
        return $this->getData('record_limit');
    }

    public function getCategoryId()
    {
        if (($categoryId = $this->getData('category_id')) && ($categoryId !== '-')){
            return $categoryId;
        }
        return false;
    }

    public function getBlockHeader()
    {
        if ($this->getCategoryId()){
            /** @var Magpleasure_Blog_Model_Category $category  */
            $category = Mage::getModel('mpblog/category');
            if (!Mage::app()->isSingleStoreMode()){
                $category->setStore(Mage::app()->getStore()->getId());
            }
            $category->load($this->getCategoryId());
            return $this->escapeHtml($category->getName());
        }
        return parent::getBlockHeader();
    }

    protected function _checkCategory($collection)
    {
        if ($this->getCategoryId()){
            /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $collection */
            $collection->addCategoryFilter($this->getCategoryId());
        }
        return $this;
    }


}