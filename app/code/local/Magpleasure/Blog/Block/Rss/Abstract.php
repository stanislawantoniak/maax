<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Rss_Abstract extends Mage_Rss_Block_Abstract
{
    protected $_collection;

    protected function _construct()
    {
        $action = $this->getRequest()->getActionName();
        $storeId = $this->getStoreId();
        $this->setCacheKey("mpblog_rss_{$action}_{$storeId}");
        $this->setCacheLifetime(600);
        parent::_construct();
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

    public function getDataCollection()
    {
        return array();
    }

    public function getStoreId()
    {
        return  $this->getRequest()->getParam('store_id') ?
                $this->getRequest()->getParam('store_id') :
                Mage::app()->getAnyStoreView()->getId();
    }

    public function createRssXml()
    {
        /** @var Mage_Rss_Model_Rss $rssObj  */
        $rssObj = Mage::getModel('rss/rss');

        $data = array(
            'title'    => $this->getRssTitle(),
            'link'     => $this->getRssUrl(),
            'charset'  => 'UTF-8',
            'atom:link' => 'test',

//            array(
//                'href' => 'http://feeds.bbci.co.uk/news/rss.xml',
//                'rel' => 'self',
//                'type' => 'application/rss+xml',
//            ),
        );

        $rssObj->_addHeader($data);

        foreach ($this->getDataCollection() as $data){
            $rssObj->_addEntry($data);
        }
        return $rssObj->createRssXml();
    }

    public function getRssUrl()
    {
        return $this->_helper()->_url()->getUrl();
    }

}