<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Layout extends Mage_Core_Block_Template
{
    const CONFIG_XML_PATH = 'mpblog/layout/%s';
    const ROUTE_LIST = 'list';
    const ROUTE_POST = 'post';

    const CACHE_DATA_PREFIX = 'mp_blog_';

    protected $_askedBlockIds = array();
    protected $_desktop = array();
    protected $_mobile = array();

    protected $_summary = array();

    protected $_messagesContent = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate("mpblog/layout.phtml");
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
     * Layout Route
     *
     * @return string
     * @throws Exception
     */
    protected function _getBlogRoute()
    {
        if ($this->getRequest()->getActionName() == 'post'){
            return self::ROUTE_POST;
        } else {
            return self::ROUTE_LIST;
        }
    }

    /**
     * Load Layout config
     *
     * @param $zone Mobile or Desktop zone
     * @param $target
     * @return $this
     * @throws Zend_Json_Exception
     */
    protected function _loadPerZoneLayoutConfig($zone, &$target)
    {
        # 1. Get Cache Helper
        $cacheHelper = $this->_helper()->getCommon()->getCache();

        # 2. Load Layout Cache
        $key = sprintf("%s_%s", $zone, $this->_getBlogRoute());
        $cacheKey = self::CACHE_DATA_PREFIX.$key."_".Mage::app()->getStore()->getId();
        $cachedConfig = $cacheHelper->getPreparedValue($cacheKey);
        if (!$cachedConfig){

            $config = Mage::getStoreConfig(sprintf(self::CONFIG_XML_PATH, $key));
            $target = Zend_Json::decode($config);
            $cacheHelper->savePreparedValue($cacheKey, $target, null, array('config'));
        } else {
            $target = $cachedConfig;
        }

        return $this;
    }

    protected function _prepareLayoutConfig()
    {
        Varien_Profiler::start('mp::blog::load_layout_config');

        $this
            ->_loadPerZoneLayoutConfig('mobile', $this->_mobile)
            ->_loadPerZoneLayoutConfig('desktop', $this->_desktop)
        ;

        Varien_Profiler::stop('mp::blog::load_layout_config');
    }

    protected function _prepareLayout()
    {
        $this->_prepareLayoutConfig();
        parent::_prepareLayout();
        return $this;
    }

    protected function _addBefore(&$target, $where, $alias)
    {
        if (isset($target[$where]) && is_array($target[$where])){
            if (!in_array($alias, $target[$where])){
                array_unshift($target[$where], $alias);
            }
        }
        return $this;
    }

    protected function _addAfter(&$target, $where, $alias)
    {
        if (isset($target[$where]) && is_array($target[$where])){
            if (!in_array($alias, $target[$where])){
                $target[$where][] = $alias;
            }
        }
        return $this;
    }

    public function addBefore($where, $alias)
    {
        $this->_addBefore($this->_desktop, $where, $alias);
        $this->_addBefore($this->_mobile, $where, $alias);

        return $this;
    }

    public function addAfter($where, $alias)
    {
        $this->_addAfter($this->_desktop, $where, $alias);
        $this->_addAfter($this->_mobile, $where, $alias);

        return $this;
    }

    protected function _isBlockUsedIn(&$target, $alias)
    {
        $where = array(
            'left_side',
            'right_side',
            'content',
        );

        foreach ($where as $listKey){
            if (isset($target[$listKey]) && is_array($target[$listKey])){
                if (in_array($alias, $target[$listKey])){
                    return true;
                }
            }
        }

        return false;
    }

    public function isBlockUsed($alias)
    {
        return $this->_isBlockUsedIn($this->_mobile, $alias) || $this->_isBlockUsedIn($this->_desktop, $alias);
    }

    public function getContentBlockHtml($alias)
    {
        /** @var Magpleasure_Blog_Block_Layout_Container $content */
        $content = $this->getChild('layout_content');
        $id = 'mpblog_content_'.str_replace("-", "_", $alias);
        if ($content && !$this->isAskedBefore($id)){

            $this->askBlock($id);
            return "<div id=\"{$id}\">".$content->getChildHtml($alias)."</div>";
        }
        return false;
    }

    public function getSidebarBlockHtml($alias)
    {
        /** @var Magpleasure_Blog_Block_Layout_Container $sidebar */
        $sidebar = $this->getChild('layout_sidebar');
        $id = 'mpblog_sidebar_'.str_replace("-", "_", $alias);
        if ($sidebar && !$this->isAskedBefore($id)){

            $this->askBlock($id);
            return "<div id=\"{$id}\">".$sidebar->getChildHtml($alias)."</div>";
        }
        return false;
    }

    public function getDesktopLayoutCode()
    {
        return isset($this->_desktop['layout']) ? $this->_desktop['layout'] : false;
    }

    public function getMobileLayoutCode()
    {
        return isset($this->_mobile['layout']) ? $this->_mobile['layout'] : false;
    }

    public function hasDesktopLeftColumn()
    {
        return in_array($this->getDesktopLayoutCode(), array('two-columns-left', 'three-columns'));
    }

    public function hasDesktopRightColumn()
    {
        return in_array($this->getDesktopLayoutCode(), array('two-columns-right', 'three-columns'));
    }

    public function hasMobileLeftColumn()
    {
        return in_array($this->getMobileLayoutCode(), array('two-columns-left', 'three-columns'));
    }

    public function hasMobileRightColumn()
    {
        return in_array($this->getMobileLayoutCode(), array('two-columns-right', 'three-columns'));
    }

    public function getDesktopBlocks($column)
    {
        if (isset($this->_desktop[$column]) && $this->_desktop[$column]){
            return $this->_desktop[$column];
        } else {
            return array();
        }
    }

    public function getMobileBlocks($column)
    {
        if (isset($this->_mobile[$column]) && $this->_mobile[$column]){
            return $this->_mobile[$column];
        } else {
            return array();
        }
    }

    public function askBlock($id)
    {
        if (!in_array($id, $this->_askedBlockIds)){
            $this->_askedBlockIds[] = $id;
        }
        return $this;
    }

    public function isAskedBefore($id)
    {
        return in_array($id, $this->_askedBlockIds);
    }

    public function getAskedBlockSelector()
    {
        $selectors = array();
        foreach ($this->_askedBlockIds as $id) {
            $selectors[] = "#".$id;
        }
        return implode(", ", $selectors);
    }
}