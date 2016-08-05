<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

class Magpleasure_Common_Block_Adminhtml_Widget_Form_Container extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Adding child block with specified child's id.
     *
     * @param string $childId
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    protected function _addButtonChildBlock($childId)
    {
        /** @var Magpleasure_Common_Block_Adminhtml_Widget_Button $block */
        $block = $this->getLayout()->createBlock('magpleasure/adminhtml_widget_button');
        $this->setChild($childId, $block);
        return $block;
    }
}