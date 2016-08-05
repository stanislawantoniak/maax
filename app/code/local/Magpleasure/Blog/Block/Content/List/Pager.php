<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_List_Pager extends Mage_Page_Block_Html_Pager
{
    /** @var Magpleasure_Blog_Model_Interface */
    protected $_object = null;

    protected $_urlPostfix = null;

    public function setPagerObject(Magpleasure_Blog_Model_Interface $object)
    {
        $this->_object = $object;
        return $this;
    }

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    /**
     * Pager Object
     *
     * @return Magpleasure_Blog_Model_Interface
     */
    public function getPagerObject()
    {
        return $this->_object;
    }

    public function getPageUrl($page)
    {
        return $this->getPagerObject()->getUrl(null, $page).$this->getUrlPostfix();
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isOldStyle()
    {
        return false;
    }

    public function getColorClass()
    {
        return $this->_helper()->getIconColorClass();
    }

    /**
     * Get Url Postfix
     *
     * @return null
     */
    public function getUrlPostfix()
    {
        return $this->_urlPostfix;
    }

    /**
     * Set URL postfix
     *
     * @param $urlPostfix
     * @return $this
     */
    public function setUrlPostfix($urlPostfix)
    {
        $this->_urlPostfix = $urlPostfix;
        return $this;
    }

    /**
     * Return current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        if (is_object($this->_collection)) {
            return $this->_collection->getCurPage();
        }

        $pageNum = (int) $this->getRequest()->getParam($this->getPageVarName());
        return $pageNum ? $pageNum : 1;
    }
}