<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Url extends Mage_Core_Helper_Abstract
{

    const ROUTE_POST = 'post';
    const ROUTE_CATEGORY = 'category';
    const ROUTE_TAG = 'tag';
    const ROUTE_ARCHIVE = 'archive';
    const ROUTE_SEARCH = 'search';

    protected $_storeId;

    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getRoute()
    {
        return trim(Mage::getStoreConfig('mpblog/seo/route', $this->getStoreId() ? $this->getStoreId() : Mage::app()->getStore()->getId()), "/");
    }

    public function getUrlPostfix($page = 1)
    {
        $postfix = $this->_helper()->getBlogPostfix();
        if ($page > 1){
            return "/{$page}{$postfix}";
        } else {
            return $postfix;
        }

    }

    public function getUrl($id = null, $route = self::ROUTE_POST, $page = 1)
    {
        $storeId = $this->getStoreId() ? $this->getStoreId() : Mage::app()->getStore()->getId();
        $baseUrl = Mage::app()->getStore($storeId)->getBaseUrl();
        $url = $baseUrl.$this->getRoute();

        if ($id){

            if ($route == self::ROUTE_POST){

                /** @var Magpleasure_Blog_Model_Post $post  */
                $post = Mage::getModel('mpblog/post');
                $post->load($id);
                if ($post->getUrlKey()){
                    $url .= "/".$post->getUrlKey();
                }


            } elseif ($route == self::ROUTE_CATEGORY) {

                /** @var Magpleasure_Blog_Model_Category $category  */
                $category = Mage::getModel('mpblog/category');
                $category->load($id);
                if ($category->getUrlKey()){
                    $url .= "/".self::ROUTE_CATEGORY."/".$category->getUrlKey();
                }                

            } elseif ($route == self::ROUTE_TAG) {
                /** @var Magpleasure_Blog_Model_Tag $category  */
                $tag = Mage::getModel('mpblog/tag');
                $tag->load($id);
                $url .= "/".self::ROUTE_TAG."/". urlencode($tag->getUrlKey());

            } elseif ($route == self::ROUTE_ARCHIVE) {

                $url .= "/".self::ROUTE_ARCHIVE."/". $id;

            }

        } else {

            if ($route == self::ROUTE_SEARCH){

                $url .= "/".self::ROUTE_SEARCH;
            }
        }

        $url .= $this->getUrlPostfix($page);

        return $url;
    }

    protected function _cleanUrl($identifier, $page = 1)
    {
        $clean = substr($identifier, strlen($this->getRoute()), strlen($identifier));
        $clean = trim($clean, "/");
        $clean = str_replace(array(
            $this->getUrlPostfix($page),
        ), "", $clean);
        return $clean;
    }

    /**
     * Retrieves Post Id or NULL
     *
     * @param string$identifier
     * @param bool|null $forceStore
     * @return integer
     */
    public function getPostId($identifier, $forceStore = false)
    {
        $clean = $this->_cleanUrl($identifier);

        /** @var Magpleasure_Blog_Model_Mysql4_Post_Collection $collection  */
        $collection = Mage::getModel('mpblog/post')->getCollection();

        $collection
            ->addFieldToFilter('url_key', $clean)
            ->addFieldToFilter('status', array('in' => array(Magpleasure_Blog_Model_Post::STATUS_ENABLED, Magpleasure_Blog_Model_Post::STATUS_HIDDEN)))
            ->setUrlKeyIsNotNull()
        ;

        if (!Mage::app()->isSingleStoreMode() && !$forceStore){
            $collection->addStoreFilter(Mage::app()->getStore()->getId());
        }

        foreach ($collection as $post){
            return $post->getId();
        }

        return false;
    }

    public function getCategoryId($identifier, $page = 1)
    {
        $clean = $this->_cleanUrl($identifier, $page);

        if (strpos($clean, "/") === false){
            return false;
        }

        $parts = explode("/", $clean);

        if ( (count($parts) != 2) || $parts[0] !== self::ROUTE_CATEGORY ){
            return false;
        }

        $categoryUrlKey = $parts[1];

        /** @var Magpleasure_Blog_Model_Mysql4_Category_Collection $collection  */
        $collection = Mage::getModel('mpblog/category')->getCollection();

        $collection
            ->addFieldToFilter('status', Magpleasure_Blog_Model_Category::STATUS_ENABLED)
            ->addFieldToFilter('url_key', $categoryUrlKey)
        ;

        if (!Mage::app()->isSingleStoreMode()){
            $collection->addStoreFilter(Mage::app()->getStore()->getId());
        }

        foreach ($collection as $category){
            return $category->getId();
        }

        return false;
    }


    public function getTagId($identifier, $page = 1)
    {
        $clean = $this->_cleanUrl($identifier, $page);

        if (strpos($clean, "/") === false){
            return false;
        }

        $parts = explode("/", $clean);

        if ( (count($parts) != 2) || $parts[0] !== self::ROUTE_TAG ){
            return false;
        }

        $tagUrlKey = $parts[1];

        /** @var Magpleasure_Blog_Model_Tag $tag  */
        $tag = Mage::getModel('mpblog/tag');

        $tagUrlKey = urldecode($tagUrlKey);
        $tag->load($tagUrlKey, 'url_key');

        if ($tag->getId()){
            return $tag->getId();
        }

        return false;
    }

    public function getArchiveId($identifier, $page = 1)
    {
        $clean = $this->_cleanUrl($identifier, $page);

        if (strpos($clean, "/") === false){
            return false;
        }

        $parts = explode("/", $clean);

        if ( (count($parts) != 2) || $parts[0] !== self::ROUTE_ARCHIVE ){
            return false;
        }

        $archiveId = $parts[1];

        /** @var Magpleasure_Blog_Model_Archive $archive  */
        $archive = Mage::getModel('mpblog/archive');

        $archiveId = urldecode($archiveId);
        $archive->load($archiveId);

        if ($archive->getId()){
            return $archive->getId();
        }

        return false;
    }

    public function getIsSearchRequest($identifier, $page = 1)
    {
        $clean = $this->_cleanUrl($identifier, $page);

        if (strpos($clean, "/") !== false){
            return false;
        }

        if ($clean === self::ROUTE_SEARCH){
            return true;
        }

        return false;
    }

    /**
     * Check Index Request
     *
     * @param string $identifier
     * @param int $page
     * @return bool
     */
    public function isIndexRequest($identifier, $page = 1)
    {
        return str_replace(array(
            $this->getUrlPostfix($page),
            '/',
            '.html',
            '.htm',
        ), "", $identifier) == $this->getRoute();
    }

    protected function _getPageVarName()
    {
        return Mage::getBlockSingleton('page/html_pager') ? Mage::getBlockSingleton('page/html_pager')->getPageVarName() : 'p';
    }

    /**
     * Check request syntax
     *
     * @param string $identifier
     * @param null|int $postId
     * @param string $route
     * @param int $page
     * @return bool
     */
    public function isRightSyntax($identifier, $postId = null, $route = self::ROUTE_POST, $page = 1)
    {
        if (!$this->_helper()->getRedirectToSeoFormattedUrl()){
            return true;
        }

        $stdPage = !!Mage::app()->getRequest()->getParam($this->_getPageVarName());
        $required = str_replace(Mage::getBaseUrl(), "", $this->getUrl($postId, $route, $page));
        return (strtolower($identifier) == strtolower($required) && !$stdPage);
    }

    /**
     * Url Key Generator
     *
     * @return Magpleasure_Blog_Helper_Url_Key
     */
    public function _key()
    {
        return Mage::helper('mpblog/url_key');
    }

    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    public function getStoreId()
    {
        return $this->_storeId;
    }
}