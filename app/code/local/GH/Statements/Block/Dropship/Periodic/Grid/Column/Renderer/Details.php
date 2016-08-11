<?php

class GH_Statements_Block_Dropship_Periodic_Grid_Column_Renderer_Details
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Currency
{
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
    public function render(Varien_Object $row) {
        $column = $this->getColumn();
        $wrapperStart = '<div class="wrapper-details wrapper-details-'.$column->getIndex().'">';
        $wrapperEnd   = '</div>';

        $text = $wrapperStart;
        foreach ($column->getRendererData() as $item) {
            $text .=
                '<div class="' . (isset($item['css_class']) ? $item['css_class'] : '') . '">' .
                    '<div class="left-detail">' . $item['text'] . '</div>' .
                    '<div class="right-detail text-right">' . $this->getFormattedValue($row->getData($item['index']), $row) . '</div>'.
                '</div>';
        }
        return $text . $wrapperEnd;
	}

    protected function getFormattedValue($value, $row) {
        $currency_code = $this->_getCurrencyCode($row);
        $data = floatval($value) * $this->_getRate($row);
        $data = sprintf("%f", $data);
        $data = Mage::app()->getLocale()->currency($currency_code)->toCurrency($data);
        return $data;
    }
}