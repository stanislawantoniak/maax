<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Preview extends Mage_Core_Block_Template
{

    /**
     * Blog Pro Helper
     *
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getHeader()
    {
        return $this->getRequest()->getPost('header');
    }

    public function hasThumbnail()
    {
        $src =
            $this->getRequest()->getPost('post_thumbnail') ?
                $this->getRequest()->getPost('post_thumbnail') :
                $this->getRequest()->getPost('list_thumbnail')
        ;

        return !!$src;
    }

    public function getThumbnailSrc()
    {
        $src =
            $this->getRequest()->getPost('post_thumbnail') ?
            $this->getRequest()->getPost('post_thumbnail') :
            $this->getRequest()->getPost('list_thumbnail')
            ;

        if ($src){
            $imageHelper = $this->_helper()->getCommon()->getImage();
            return $imageHelper->init($src)->__toString();
        }

        return false;
    }

    public function getContent()
    {
        /** @var $post Magpleasure_Blog_Model_Post */
        $post = Mage::getModel('mpblog/post');

        $post->setData('full_content', $this->getRequest()->getPost('content'));
        return $post->getFullContent();
    }

    public function getWidth()
    {
        return $this->getRequest()->getPost('width');
    }

    public function getHeight()
    {
        return $this->getRequest()->getPost('height');
    }
}