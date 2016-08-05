<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Social_Wrapper extends Magpleasure_Blog_Block_Content_Post
{

    const MAX_IMAGE_WIDTH = 1000;
    const MAX_IMAGE_HEIGHT = 1000;

    protected $_collection;

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'social_wrapper';
        return $params;
    }

    public function isGooglePlusEnabled()
    {
        return (in_array('googleplus', $this->_helper()->getSocialNetworks()));
    }

    public function isFacebookPlusEnabled()
    {
        return (in_array('facebook', $this->_helper()->getSocialNetworks()));
    }

    public function getPostTitle()
    {
        return htmlspecialchars($this->getPost()->getTitle());
    }

    public function getPostMetaDescription()
    {
        $text = htmlspecialchars(str_replace("\n", "", $this->getPost()->getMetaDescription()));
        $text = $this->_helper()->getCommon()->getStrings()->strLimit($text, 200, false);
        return $text;
    }

    public function getPostUrl()
    {
        return $this->getPost()->getPostUrl();
    }

    public function hasThumbnail()
    {
        return !!$this->getPost()->getPostThumbnail() || !!$this->getPost()->getListThumbnail();
    }

    public function getThumbnailSrc()
    {
        $imageHelper = $this->_helper()->getCommon()->getImage();

        $src =  $this->getPost()->getPostThumbnail() ? $this->getPost()->getPostThumbnail() : $this->getPost()->getListThumbnail();
        if ($src){
            $imageHelper->init( str_replace("/", DS, $src) );
            return $imageHelper->setMaxSize(self::MAX_IMAGE_WIDTH, self::MAX_IMAGE_HEIGHT)->keepFrame(false);
        }
    }

    public function getSiteName()
    {
        return Mage::app()->getStore()->getFrontendName();
    }

    public function getPublisherFacebook()
    {
        return $this->_helper()->getPublisherFacebook();
    }

    public function getAuthorFacebook()
    {
        return $this->getPost()->getFacebookProfile();
    }

    public function getPublisherTwitter()
    {
        return $this->_helper()->getPublisherTwitter();
    }

    public function getAuthorTwitter()
    {
        return $this->getPost()->getTwitterProfile();
    }

    public function getPostCategories()
    {
        $categories = $this->getPost()->getCategories();
        if (count($categories)){
            foreach ($categories as $categoryId){
                return $this->_helper()->getCategoryHelper()->getCategoryName($categoryId);
            }
        }
    }

    public function getPostTags()
    {
        if (!$this->_tags){
            /** @var Magpleasure_Blog_Model_Mysql4_Tag_Collection  $collection  */
            $collection = Mage::getModel('mpblog/tag')->getCollection();
            $store = Mage::app()->isSingleStoreMode() ? null : Mage::app()->getStore()->getId();
            $collection
                ->addPostFilter(
                    $this->getPost()->getId()
                )
                ->addWieghtData(
                    $store
                )
                ->setMinimalPostCountFilter(
                    $this->_helper()->getTagsMinimalPostCount()
                )
                ->setPostStatusFilter(
                    Magpleasure_Blog_Model_Post::STATUS_ENABLED
                )
                ->setNameOrder()
            ;

            $this->_tags = $collection;
        }
        return $this->_tags;
    }

    protected function _prepareISO86O1Time($datetime)
    {
        return str_replace(" ", "T", $datetime).$this->_helper()->getTimeZoneOffset(true);
    }

    public function getPostPublishedDate()
    {
        return $this->_prepareISO86O1Time($this->getPost()->getPublishedAt());
    }

    public function getPostModifiedDate()
    {
        return $this->_prepareISO86O1Time($this->getPost()->getUpdatedAt());
    }
}