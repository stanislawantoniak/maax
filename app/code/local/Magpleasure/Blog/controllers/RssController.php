<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_RssController extends Mage_Core_Controller_Front_Action
{
    /**
     * Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    public function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function indexAction()
    {
        $params = $this->getRequest()->getParams();
        $url = $this->_helper()->getCommon()->getRequest()->getUrlModel()->getUrl('mpblog/rss/post', $params);
        $this->getResponse()->setRedirect($url, 302);
    }

    public function categoryAction()
    {
        if (!$this->_helper()->getRssPost($this->getRequest()->getParam('store_id'))){
            $this->getResponse()->setRedirect(Mage::getBaseUrl('web'), 302)->sendHeaders();
            return;
        }
        $this->getResponse()->setHeader('Content-Type', 'text/xml')->setBody($this->_getContent('category'));
    }

    public function tagAction()
    {
        if (!$this->_helper()->getRssPost($this->getRequest()->getParam('store_id'))){
            $this->getResponse()->setRedirect(Mage::getBaseUrl('web'), 302)->sendHeaders();
            return;
        }
        $this->getResponse()->setHeader('Content-Type', 'text/xml')->setBody($this->_getContent('tag'));
    }

    public function postAction()
    {
        if (!$this->_helper()->getRssPost($this->getRequest()->getParam('store_id'))){
            $this->getResponse()->setRedirect(Mage::getBaseUrl('web'), 302)->sendHeaders();
            return;
        }
        $this->getResponse()->setHeader('Content-Type', 'text/xml')->setBody($this->_getContent('post'));
    }

    public function commentAction()
    {
        if (!$this->_helper()->getRssComment($this->getRequest()->getParam('store_id'))){
            $this->getResponse()->setRedirect(Mage::getBaseUrl('web'), 302)->sendHeaders();
            return;
        }

        $postId = $this->getRequest()->getParam("post_id");
        $params = array();
        if ($postId){

            $params["post_id"] = $postId;
        }

        $this
            ->getResponse()
            ->setHeader('Content-Type', 'text/xml')
            ->setBody(
                $this->_getContent('comment', $params)
            )
        ;
    }

    protected function _getContent($route, array $params = array())
    {
        /** @var Magpleasure_Blog_Block_Rss_Abstract $block  */
        $block = $this->getLayout()->createBlock("mpblog/rss_{$route}");
        if ($block){

            $block->addData($params);

            $xml = $block->createRssXml();
            $rss = new SimpleXMLElement($xml);

            /** @var SimpleXMLElement $channel */
            $channel = $rss->channel;
            $placeholderContent = "__placeholder__";
            $atomLink = $channel->addChild($placeholderContent);

            $currentUrl = $this->_helper()->getCommon()->getRequest()->getCurrentManegtoUrl();
            $atomLink->addAttribute('href', $currentUrl);
            $atomLink->addAttribute('rel', 'self');
            $atomLink->addAttribute('type', 'application/rss+xml');

            $xml = $rss->asXML();
            $xml = str_replace($placeholderContent, "atom:link", $xml);
            $xml = str_replace('<rss ', '<rss  xmlns:atom="http://www.w3.org/2005/Atom"  ', $xml);

            return $xml;
        }
        return false;
    }

}