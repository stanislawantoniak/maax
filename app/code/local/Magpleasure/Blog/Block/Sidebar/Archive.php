<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Archive extends Magpleasure_Blog_Block_Sidebar_Abstract
{
    protected $_collection;
    protected $_archive;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/sidebar/archive.phtml");
        $this->_route = 'display_archives';
    }


    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'archive';

        if ($this->_isRequestMatchParams('mpblog', 'index', 'archive')){
            $params[] = 1;
            $params[] = $this->getRequest()->getParam('id');
        } else {
            $params[] = 0;
        }

        return $params;
    }

    public function getLimit()
    {
        return $this->getArchive()->getLimit();
    }

    protected function _getMonth()
    {
        return $this->getRequest()->getParam('month');
    }

    protected function _getYear()
    {
        return $this->getRequest()->getParam('year');
    }

    public function isArchivePage()
    {
        return $this->getArchive()->getId() &&
               ($this->getRequest()->getModuleName() == 'mpblog') &&
               ($this->getRequest()->getActionName() == 'archive')
               ;
    }

    public function getBlockHeader()
    {
        return $this->__("Archives");
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

    public function getCollection()
    {
        if (!$this->_collection){
            $collection = $this->getArchive()->getArchives();
            $this->_collection = $collection;
        }
        return $this->_collection;
    }

    public function getIsActive(Magpleasure_Blog_Model_Archive $archive)
    {
        return ($archive->getId() == $this->getRequest()->getParam('id'));
    }
}