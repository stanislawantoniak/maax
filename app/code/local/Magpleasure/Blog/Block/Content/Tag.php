<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Tag extends Magpleasure_Blog_Block_Content_List
{
    protected $_tag;

    protected function _construct()
    {
        $this->_isTag = true;
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getToolbar()->setPagerObject($this->getTag());
        return $this;
    }

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'tag';
        return $params;
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

            $breadcrumbs->addCrumb($this->getTag()->getUrlKey(), array(
                'label' => $this->getTitle(),
                'title' => $this->getTitle(),
            ));
        }
    }

    public function getTitle()
    {
        return $this->__("Posts tagged '%s'", $this->getTag()->getName());
    }

    public function getPageHeader()
    {
        return $this->getTitle();
    }

    public function getMetaTitle()
    {
        return $this->getTag()->getMetaTitle() ? $this->getTag()->getMetaTitle() : $this->_helper()->checkForPrefix($this->getTitle());
    }

    public function getKeywords()
    {
        return $this->getTag()->getMetaTags();
    }

    public function getDescription()
    {
        return $this->getTag()->getMetaDescription();
    }

    public function getTag()
    {
        if (!$this->_tag){
            /** @var Magpleasure_Blog_Model_Tag $tag  */
            $tag = Mage::getModel('mpblog/tag');
            $tag->load($this->getRequest()->getParam('id'));
            $this->_tag = $tag;
        }
        return $this->_tag;
    }

}