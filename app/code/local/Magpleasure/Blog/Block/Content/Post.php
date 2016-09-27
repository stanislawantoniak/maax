<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Post extends Magpleasure_Blog_Block_Content_Abstract
{
    const CACHE_PREFIX = 'mpblog_post_';

    protected $_cacheParams = array();

    protected function _construct()
    {
        $this->addData(array(
            'cache_lifetime'    => 2600,
            'cache_tags'        => array(
                Magpleasure_Common_Helper_Cache::MAGPLEASURE_CACHE_KEY,
                'CONFIG',
                Magpleasure_Blog_Model_Post::CACHE_TAG."_".$this->getRequest()->getParam("id"),
            ),
            'cache_key'         => $this->getCacheKey(),
        ));

        parent::_construct();

        $this->setTemplate('mpblog/post.phtml');
    }

    public function getCacheKey()
    {
        return self::CACHE_PREFIX.md5(implode($this->_getCacheParams()));
    }

    protected function _getCacheParams()
    {
        $dynamicCommentIds = $this->_helper()->getCommon()->getCookie()->getAllFromCookie($this->_helper()->getDynamicCookieName());

        $params = array(
            Mage::app()->getStore()->getId(),
            $this->getPost()->getId(),
            implode("_", $dynamicCommentIds),
        );

        return  $params;
    }

    /**
     * Post
     * @return Magpleasure_Blog_Model_Post
     */
    public function getPost()
    {
        if (!Mage::registry('current_post')){
            if ($postId = $this->getRequest()->getParam('id')){
                /** @var Magpleasure_Blog_Model_Post $post  */
                $post = Mage::getModel('mpblog/post');
                if (!Mage::app()->isSingleStoreMode()){
                    $post->setStore(Mage::app()->getStore()->getId());
                }
                $post->load($postId);
                Mage::register('current_post', $post, true);
            } else {
                Mage::throwException($this->__("Unknown post id."));
            }
        }
        return Mage::registry('current_post');
    }

    protected function _prepareLayout()
    {
        $this->_title = $this->getPost()->getTitle();
        parent::_prepareLayout();
        return $this;
    }

    public function getMetaTitle()
    {
        return $this->getPost()->getMetaTitle() ? $this->getPost()->getMetaTitle() : $this->_helper()->checkForPrefix($this->getPost()->getTitle());
    }

    public function getDescription()
    {
        return $this->getPost()->getMetaDescription();
    }

    public function getKeywords()
    {
        return $this->getPost()->getMetaTags();
    }

    protected function _prepareBreadcrumbs()
    {
        parent::_prepareBreadcrumbs();

        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs){
            $breadcrumbs->addCrumb('blog', array(
                'label' => $this->_helper()->getMenuLabel(),
                'title' => $this->_helper()->getMenuLabel(),
                'link' => $this->_helper()->_url()->getUrl(),
            ));

            $breadcrumbs->addCrumb('post', array(
                'label' => $this->getTitle(),
                'title' => $this->getTitle(),
            ));
        }
    }

    public function getCommentsActionHtml()
    {
        return $this->getChildHtml('mpblog_comments_action');
    }

    public function getCommentsHtml()
    {
        return $this->getChildHtml('mpblog_comments_list');
    }

    public function getSocialHtml()
    {
        return $this->getChildHtml('mpblog_social');
    }

    public function getColorClass()
    {
        return $this->_helper()->getIconColorClass();
    }

    public function getShowPrintLink()
    {
        return $this->_helper()->getShowPrintLink();
    }

    public function hasThumbnailUrl()
    {
        return !!$this->getPost()->getThumbnailUrl();
    }

    public function getThumbnailUrl()
    {
        $url = $this->getPost()->getThumbnailUrl();

        $processor = Mage::getModel('cms/template_filter');
        $url = $processor->filter($url);

        return $url;
    }
}