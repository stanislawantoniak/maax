<?php

/**
 * Class Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Price
 */
class Zolago_Adminhtml_Block_Widget_Grid_Column_Filter_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Price
{

    public function getHtml()
    {
        $html  = '<div class="range">';
        $html .= '<div class="range-line"><span class="label">' . Mage::helper('adminhtml')->__('From').':</span> <input type="text" name="'.$this->_getHtmlName().'[from]" id="'.$this->_getHtmlId().'_from" value="'.$this->getEscapedValue('from').'" class="input-text no-changes form-control"/></div>';
        $html .= '<div class="range-line"><span class="label">' . Mage::helper('adminhtml')->__('To').' : </span><input type="text" name="'.$this->_getHtmlName().'[to]" id="'.$this->_getHtmlId().'_to" value="'.$this->getEscapedValue('to').'" class="input-text no-changes form-control"/></div>';
        if ($this->getDisplayCurrencySelect())
            $html .= '<div class="range-line"><span class="label">' . Mage::helper('adminhtml')->__('In').' : </span>' . $this->_getCurrencySelectHtml() . '</div>';
        $html .= '</div>';

        return $html;
    }

    protected function _getCurrencySelectHtml()
    {

        $value = $this->getEscapedValue('currency');
        if (!$value)
            $value = $this->getColumn()->getCurrencyCode();

        $html  = '';
        $html .= '<select name="'.$this->_getHtmlName().'[currency]" id="'.$this->_getHtmlId().'_currency" class="form-control">';
        foreach ($this->_getCurrencyList() as $currency) {
            $html .= '<option value="' . $currency . '" '.($currency == $value ? 'selected="selected"' : '').'>' . $currency . '</option>';
        }
        $html .= '</select>';
        return $html;
    }

}
