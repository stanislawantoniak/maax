<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Category extends Magpleasure_Blog_Block_Content_List
{
    protected $_category;

    protected function _construct()
    {
        $this->_isCategory = true;
        parent::_construct();
    }

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'category';
        return $params;
    }

    protected function _prepareLayout()
    {
        $this->_title = $this->getCategory()->getTitle();
        parent::_prepareLayout();
        $this->getToolbar()->setPagerObject($this->getCategory());
        return $this;
    }

    protected function _prepareBreadcrumbs()
    {
        parent::_prepareBreadcrumbs();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs){
            $breadcrumbs->addCrumb('blog', array(
                'label' => $this->_helper()->getMenuLabel(),
                'title' => $this->_helper()->getMenuLabel(),
                'link' =>  $this->_helper()->_url()->getUrl(),
            ));

            $breadcrumbs->addCrumb($this->getCategory()->getUrlKey(), array(
                'label' => $this->getCategory()->getName(),
                'title' => $this->getCategory()->getName(),
            ));
        }
    }

    public function getPageHeader()
    {
        return $this->getCategory()->getName();
    }

    public function getMetaTitle()
    {
        return $this->getCategory()->getMetaTitle() ? $this->getCategory()->getMetaTitle() : $this->_helper()->checkForPrefix($this->getCategory()->getName());
    }

    public function getDescription()
    {
        return $this->getCategory()->getMetaDescription();
    }

    public function getKeywords()
    {
        return $this->getCategory()->getMetaTags();
    }

    public function getCategory()
    {
        if (!$this->_category){
            /** @var Magpleasure_Blog_Model_Category $category  */
            $category = Mage::getModel('mpblog/category')->load($this->getRequest()->getParam('id'));
            $this->_category = $category;
        }
        return $this->_category;
    }

}