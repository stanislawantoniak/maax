<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Social extends Magpleasure_Blog_Block_Content_Post
{

    protected function _getCacheParams()
    {
        $params = parent::_getCacheParams();
        $params[] = 'social';
        return $params;
    }

    /**
     * Social Networks
     *
     * @return array
     */
    public function getButtons()
    {
        /** @var Magpleasure_Blog_Model_Networks $networks  */
        $networks = Mage::getModel('mpblog/networks');
        return $networks->getNetworks();
    }

    public function getButtonsCount()
    {
        return count($this->getButtons());
    }

    public function getButtonUrl($button)
    {
        $url = $button->getUrl();

        $url = str_replace("{url}", urlencode($this->getPost()->getPostUrl()), $url);
        $url = str_replace("{title}", urlencode($this->getPost()->getTitle()), $url);
        $url = str_replace("{description}", urlencode($this->getPost()->getMetaDescription()), $url);

        if ($button->getImage()){
            $url = str_replace("{image}", urlencode($this->getPost()->getPostThumbnailSrc()), $url);
        }

        return $url;
    }

    public function getButtonHtml($button)
    {
        if ($key = $button->getValue()){
            /** @var Mage_Core_Block_Template $block  */
            $block = $this->getLayout()->createBlock('mpblog/social_button');
            if ($block){
                $block
                    ->setTemplate("mpblog/social/{$key}.phtml")
                    ->setButton($button)
                    ;
                return $block->toHtml();
            }
        }
        return '';
    }

    public function getHasImage($button)
    {
        return ($button->getImage() && !!$this->getPost()->hasThumbnail()) ||
                !$button->getImage();
    }

}