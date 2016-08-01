<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Sidebar_Abstract extends Magpleasure_Blog_Block_Layout_Abstract
{
    const CACHE_PREFIX = 'mpblog_sidebar_';

    /**
     * Route to get configuration
     *
     * @var string
     */
    protected $_route = 'abstract';

    /**
     * Place to define displaying
     *
     * @var string
     */
    protected $_place;

    protected $_keysToCache = array('place'); # Can be array

    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    protected function _isRequestMatchParams($moduleName, $controller, $action)
    {
        $request = $this->getRequest();
        return
            $request->getModuleName() == $moduleName &&
            $request->getControllerName() == $controller &&
            $request->getActionName() == $action ;
    }

    protected function _prepareCollectionToStart(Magpleasure_Blog_Model_Mysql4_Post_Collection $collection, $limit)
    {
        $collection
            ->setPageSize($limit)
            ->setCurPage(1)
        ;

        return $this;
    }

    protected function _construct()
    {
        parent::_construct();
    }

    protected function _beforeToHtml()
    {
        $this->addData(array(
            'cache_lifetime'    => 2600,
            'cache_tags'        => array(
                Magpleasure_Common_Helper_Cache::MAGPLEASURE_CACHE_KEY,
                'CONFIG',
            ),
            'cache_key'         => $this->getCacheKey(),
        ));

        parent::_beforeToHtml();
    }

    protected function _dataHash()
    {
        if ($this->_keysToCache && is_array($this->_keysToCache)){
            $values = array();
            foreach ($this->_keysToCache as $key){
                if ($this->getData($key)){
                    $values[] = $this->getData($key);
                }
            }
            return implode("_", $values);
        }
        return false;
    }

    public function getCacheKey()
    {
        return self::CACHE_PREFIX.md5(implode($this->_getCacheParams()));
    }

    protected function _getCacheParams()
    {
        $params = array(Mage::app()->getStore()->getId());

        $data = $this->_dataHash();
        if ($data){
            $params[] = $data;
        }

        return  $params;
    }

    /**
     * Wrapper for standard strip_tags() function with extra functionality for html entities
     *
     * @param string $data
     * @param string $allowableTags
     * @param bool $allowHtmlEntities
     * @return string
     */
    public function stripTags($data, $allowableTags = null, $allowHtmlEntities = false)
    {
        return $this->_helper()->stripTags($data, $allowableTags, $allowHtmlEntities);
    }

    /**
     * Set Place
     *
     * @param $place
     * @return Magpleasure_Blog_Block_Sidebar_Abstract
     */
    public function setPlace($place)
    {
        $this->_place = $place;
        return $this;
    }

    public function getConfPlace()
    {
        return Mage::getStoreConfig("mpblog/general/".$this->getRoute());
    }

    public function getRoute()
    {
        return $this->_route;
    }

    public function getDisplay()
    {
        return is_null($this->_place) || ($this->getConfPlace() && ($this->_place == $this->getConfPlace()));
    }

    /**
     * Backward compatibility
     *
     * @deprecated Will be removed in one of future versions
     * @param $content
     * @return string
     */
    public function getStrippedConent($content)
    {
        return $this->getStrippedContent($content);
    }

    public function getStrippedContent($content)
    {
        $limit = $this->_helper()->getRecentPostsShortLimit();

        $stringHelper = $this->_helper()->getCommon()->getStrings();
        $content = $stringHelper->htmlToPlainText($content);

        if ($stringHelper->strlen($content) > $limit){
            $content = $stringHelper->substr($content, 0, $limit);
            if ($stringHelper->strpos($content, " ") !== false){
                $cuts = explode(" ", $content);
                if (count($cuts) && count($cuts) > 1){
                    unset($cuts[count($cuts) - 1]);
                    $content = implode(" ", $cuts);
                }
            }
        }
        return $content."...";
    }

    protected function _checkCategory($collection)
    {
        return $this;
    }

    public function getHeaderHtml($post = null)
    {
        return $this->_helper()->getHeaderHtml($post);
    }

    public function getFooterHtml($post = null)
    {
        return $this->_helper()->getFooterHtml($post);
    }

    public function getColorClass()
    {
        return $this->_helper()->getIconColorClass();
    }

    /**
     * @deprecated
     * @return bool
     */
    public function isOldStyle()
    {
        return false;
    }
}
