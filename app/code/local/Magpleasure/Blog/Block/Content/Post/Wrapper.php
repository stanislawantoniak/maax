<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Content_Post_Wrapper extends Magpleasure_Blog_Block_Content_Post
{
    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'wrapper';
        return $params;
    }

    public function useGoogleProfile()
    {
        return !!$this->getPost()->getPostedBy() && !!$this->getPost()->getGoogleProfile();
    }

    public function getGoogleProfileUrl()
    {
        return $this->getPost()->getGoogleProfile();
    }

}