<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Archive extends Magpleasure_Blog_Block_Content_List
{
    protected $_archive;

    protected function _construct()
    {
        $this->_isArchive = true;
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->getToolbar()->setPagerObject($this->getArchive());
        return $this;
    }

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'archive';
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

            $breadcrumbs->addCrumb($this->getArchive()->getUrlKey(), array(
                'label' => $this->getTitle(),
                'title' => $this->getTitle(),
            ));
        }
    }

    public function getCollection()
    {
        if (!$this->_collection){

            /** @var $collection Magpleasure_Blog_Model_Mysql4_Post_Collection */
            $collection = parent::getCollection();

            $from = $this->getArchive()->getFromFilter();
            $to = $this->getArchive()->getToFilter();

            $collection
                ->addFromFilter($from)
                ->addToFilter($to)
            ;

            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function getTitle()
    {
        return $this->getArchive()->getLabel();
    }

    public function getPageHeader()
    {
        return $this->getTitle();
    }

    public function getMetaTitle()
    {
        return $this->getArchive()->getMetaTitle() ? $this->getArchive()->getMetaTitle() : $this->_helper()->checkForPrefix($this->getTitle());
    }

    public function getDescription()
    {
        $blogMetaTitle = $this->_helper()->getBlogMetaTitle();
        if ($blogMetaTitle){
            return $this->__("%s Archive in %s", $this->getPageHeader(), $blogMetaTitle);
        } else {
            return $this->__("%s Archive", $this->getPageHeader());
        }
    }

    public function getKeywords()
    {
        return implode(", ", $this->getCollection()->getCombinedKeywords());
    }

    /**
     * Archive Model
     *
     * @return Magpleasure_Blog_Model_Archive
     */
    public function getArchive()
    {
        if (!$this->_archive){
            $archive = Mage::getModel('mpblog/archive')->load($this->getRequest()->getParam('id'));
            $this->_archive = $archive;
        }
        return $this->_archive;
    }

    public function getShowRssLink()
    {
        return false;
    }
}