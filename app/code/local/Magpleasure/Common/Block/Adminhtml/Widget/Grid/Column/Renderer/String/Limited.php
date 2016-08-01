<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Magpleasure_Common
 */

/** Renderer of text with limitation of characters to output (without expand) */
class Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_String_Limited
    extends Magpleasure_Common_Block_Adminhtml_Widget_Grid_Column_Renderer_Abstract
{
    const DEFAULT_LIMIT = 200;

    protected function _limit()
    {
        return $this->getColumn()->getLimit() ? $this->getColumn()->getLimit() : self::DEFAULT_LIMIT;
    }

    public function render(Varien_Object $row)
    {
        $value = $this->_getValue($row);
        $limit = $this->_limit();

        if ($value && $this->_commonHelper()->getStrings()->strlen($value) > $limit){
            $value = $this->_commonHelper()->getStrings()->htmlToText($value);
            $value = $this->_commonHelper()->getStrings()->strLimit($value, $limit);
            $value .= "...";
        }

        return $value;
    }
}