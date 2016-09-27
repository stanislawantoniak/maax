<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Search extends Magpleasure_Blog_Block_Sidebar_Abstract
{
    protected $_search;

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'search';
        $params[] = $this->getRequest()->getParam('query');

        return $params;
    }

    /**
     * Archive Model
     *
     * @return Magpleasure_Blog_Model_Search
     */
    public function getSearch()
    {
        if (!$this->_search){
            $search = Mage::getModel('mpblog/search');
            $this->_search = $search;
        }
        return $this->_search;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/sidebar/search.phtml");
        $this->_route = 'display_search';
    }

    public function getBlockHeader()
    {
        return $this->__("Search the blog");
    }

    public function getSearchUrl()
    {
        return $this->getSearch()->getUrl();
    }

    public function getQuery()
    {
        return $this->stripTags($this->getRequest()->getParam('query'));
    }

    public function getColorClass()
    {
        return $this->_helper()->getIconColorClass();
    }
}