<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Blog
 */

class Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout_Mobile
    extends Magpleasure_Blog_Block_Adminhtml_System_Config_Field_Layout
{
    protected function _getLayouts()
    {
        $config = array(
            'two-columns-left' => $this->__("Two Columns and Left Sidebar"),
            'two-columns-right' => $this->__("Two Columns and Right Sidebar"),
        );
        return $this
            ->_helper()
            ->getCommon()
            ->getArrays()
            ->paramsToValueLabel($config)
        ;
    }
}