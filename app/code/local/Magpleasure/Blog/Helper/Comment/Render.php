<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Helper_Comment_Render extends Mage_Core_Helper_Data
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

    public function render($content)
    {
        preg_match_all('#\bhttps?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/))#', $content, $match);
        $content = $this->_helper()->escapeHtml($content);
        foreach ($match[0] as $url){

            if ($this->_helper()->getCommentsNoFollow()){

                $content = str_replace(
                    $url,
                    "<a href=\"{$url}\" rel='nofollow' target=\"_blank\">{$url}</a>",
                    $content
                );

            } else {

                $content = str_replace(
                    $url,
                    "<a href=\"{$url}\" target=\"_blank\">{$url}</a>",
                    $content
                );
            }
        }
        return nl2br($content);
    }
}