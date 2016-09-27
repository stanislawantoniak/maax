<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Desktop_Post
    extends Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Desktop
{
    protected function _getContentBlocks()
    {
        $result = parent::_getContentBlocks();

        # Add some extra staff
        $result[] = array(
            'value' => 'post',
            'label' => $this->__("Post"),
            'backend_image' => 'mpblog/images/layout/assets/post.png',
        );
        return $result;
    }
}