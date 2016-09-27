<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_Widget_Form_Short
    extends Magpleasure_Blog_Block_Adminhtml_Widget_Form_Wysiwyg
{
    /**
     * Helper
     * @return Magpleasure_Blog_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('mpblog');
    }

    public function getShowCutter()
    {
        return false;
    }

    public function getElementHtml()
    {
        $editorHtml = parent::getElementHtml();
        $displayIt = $this->getData('display_short_content');
        $showLabel = $this->_helper()->__("Enable Short Content");
        $hideLabel = $this->_helper()->__("Disable Short Content");
        $showStyle = "";
        $hideStyle = "";
        $editorStyle = "";


        if ($displayIt){
            $showStyle = ' style="display: none; padding-bottom: 10px;"';
            $hideStyle = ' style="display: block; padding-bottom: 10px;"';
        } else {
            $hideStyle = ' style="display: none; padding-bottom: 10px;"';
            $showStyle = ' style="style="display: block; padding-bottom: 10px;"';
            $editorStyle = ' style="display: none;"';
        }

        return "
        <input type=\"hidden\" name=\"display_short_content\" id=\"display_short_content\" value=\"{$displayIt}\" />
        <div><a href=\"#\" onclick=\"showShortContent(); return false;\" id=\"show_short_content\"{$showStyle}>{$showLabel}</a><a href=\"#\" onclick=\"hideShortContent(); return false;\" id=\"hide_short_content\"{$hideStyle}>{$hideLabel}</a></div>
        <div id=\"short_content_editor_container\"{$editorStyle}>{$editorHtml}</div>
        <script type=\"text/javascript\">

            var showShortContent = function(){
                Effect.Appear('short_content_editor_container', {duration: 0.5, afterFinish: function(e){
                    $('show_short_content').style.display = 'none';
                    $('hide_short_content').style.display = 'block';
                    $('display_short_content').value = '1';
                }});
            };

            var hideShortContent = function(){
                Effect.Fade('short_content_editor_container',{duration: 0.3, afterFinish: function(e){
                    $('show_short_content').style.display = 'block';
                    $('hide_short_content').style.display = 'none';
                    $('display_short_content').value = '0';
                }});
            };

        </script>
        ";
    }


}