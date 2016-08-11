<?php
/**
 *
 */
class Zolago_Adminhtml_Block_Widget_Grid_Column_Filter_Text extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getHtml()
    {
        $html = '<div class="field-100"><input type="text" name="'.$this->_getHtmlName().'" id="'.$this->_getHtmlId().'" value="'.$this->getEscapedValue().'" class="input-text no-changes form-control"/></div>';
        return $html;
    }
}