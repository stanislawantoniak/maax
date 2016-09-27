<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class  Magpleasure_Blog_Block_Adminhtml_Widget_Form_Wysiwyg_Renderer
    extends Magpleasure_Common_Block_Adminhtml_Template
{
    const MIN_HEIGHT = 150; # px

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('mpblog/system/wysiwyg/template.phtml');
    }

    public function getAutoSaveUrl()
    {
        $params = array('form_key' => $this->getFormKey());
        if ($this->getPostId()){
            $params['id'] = $this->getPostId();
        }

        return $this->getUrl('adminhtml/mpblog_post/autosave', $params);
    }

    public function getImageUploadUrl()
    {
        return $this->getUrl('adminhtml/mpblog_post/uploadImage');
    }

    public function getImageJsonUrl()
    {
        return $this->getUrl('adminhtml/mpblog_post/imageListJson');
    }

    public function getMagentoLinksJsonUrl()
    {
        return $this->getUrl('adminhtml/mpblog_post/magentoLinksJson');
    }

    public function getImageClipboardUrl()
    {
        return $this->getUrl('adminhtml/mpblog_post/clipboard');
    }

    public function getMinHeight()
    {
        return $this->getData('min_height') ? $this->getData('min_height') : self::MIN_HEIGHT;
    }

    public function getPostId()
    {
        return Mage::registry('current_post') ? Mage::registry('current_post')->getId() : falses;
    }
}