<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Mobile_List
    extends Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Mobile
{
    protected function _getContentBlocks()
    {
        $result = parent::_getContentBlocks();
        # Add some extra staff
        $result[] = array(
            'value' => 'list',
            'label' => $this->__("List"),
            'backend_image' => 'mpblog/images/layout/assets/list_list.png',

        );
        $result[] = array(
            'value' => 'grid',
            'label' => $this->__("Grid"),
            'backend_image' => 'mpblog/images/layout/assets/list_grid.png',
        );
        return $result;
    }
}