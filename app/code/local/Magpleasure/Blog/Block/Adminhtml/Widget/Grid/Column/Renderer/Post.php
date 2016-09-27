<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */


class Magpleasure_Blog_Block_Adminhtml_Widget_Grid_Column_Renderer_Post
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        $postId = $this->_getValue($row);
        if ($postId) {
            $html = "";
            /** @var Magpleasure_Blog_Model_Post $post  */
            $post = Mage::getModel('mpblog/post')->load($postId);
            $title = $this->escapeHtml($post->getTitle());

            $params = array(
                'id' => $postId
            );

            $storeId = $this->getRequest()->getParam('store');
            if ($storeId !== null){
                $params['store'] = $storeId;
            }

            $url = $this->getUrl('adminhtml/mpblog_post/edit', $params);
            $html .= "<a href=\"{$url}\" target=\"_blank\">{$title}</a>";
            return $html;
        }
        return parent::render($row);
    }



}