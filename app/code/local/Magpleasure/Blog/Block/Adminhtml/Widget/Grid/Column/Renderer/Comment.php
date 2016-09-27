<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Comment extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    const DISPLAY_LIMIT = 100;

    public function _cutBadSuffix($content)
    {
        $contentPieces = explode(" ", $content);
        if (count($contentPieces) > 1){
            unset($contentPieces[count($contentPieces) - 1]);
        }
        $content = implode(" ", $contentPieces);
        return $content;
    }

    protected function _getMoreContent($content, $fullContent)
    {
        /** @var Magpleasure_Blog_Helper_Data $helper  */
        $helper = Mage::helper('mpblog');
        $more = str_replace($content, "", $fullContent);
        $id = 'hidden-content-'.rand(0, 1000000);
        $more = '<div class="hidden-content" style="display: none;" id="'.$id.'">'.$more.'</div>';
        $more .= '<div><a id="a-'.$id.'-show" href="#" onclick="moreComments(\''.$id.'\'); return false;">'.$helper->__("Show more").'</a>'.
                 '<a id="a-'.$id.'-hide" href="#" style="display: none;" onclick="moreComments(\''.$id.'\'); return false;">'.$helper->__("Show less").'</a></div>';
        return $more;
    }

    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $content = $this->_getValue($row);
        if ($content){
            /** @var Magpleasure_Blog_Helper_Data $helper  */
            $helper = Mage::helper('mpblog');
            $fullContent = $content = $helper->_render()->render($content);

            if (function_exists('mb_strlen')){
                if (mb_strlen($content, 'UTF-8') > self::DISPLAY_LIMIT){
                    $content = $this->_cutBadSuffix(mb_substr($content, 0, self::DISPLAY_LIMIT - 1, 'UTF-8'));
                    $content .= $this->_getMoreContent($content, $fullContent);
                }
            } else {
                if (strlen($content) > self::DISPLAY_LIMIT){
                    $content = $this->_cutBadSuffix(substr($content, 0, self::DISPLAY_LIMIT - 1));
                    $content .= $this->_getMoreContent($content, $fullContent);
                }
            }

            return $content;
        }
        return parent::render($row);
    }
}