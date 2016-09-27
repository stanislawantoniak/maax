<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Form_Wysiwyg_Renderer extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('magpleasure/widget/form/wysiwyg.phtml');
    }

    public function getTinyThemeJsUrl()
    {
        return Mage::getBaseUrl('js')."magpleasure/libs/tinymce/themes/modern/theme.min.js";
    }

    public function getTinySkinUrl()
    {
        return Mage::getBaseUrl('js')."magpleasure/libs/tinymce/skins/lightgray/skin.min.css";
    }

    public function getTinyContentUrl()
    {
        return Mage::getBaseUrl('js')."magpleasure/libs/tinymce/skins/lightgray/content.min.css";
    }
}