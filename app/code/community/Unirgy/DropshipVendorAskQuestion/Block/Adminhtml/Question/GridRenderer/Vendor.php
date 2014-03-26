<?php

class Unirgy_DropshipVendorAskQuestion_Block_Adminhtml_Question_GridRenderer_Vendor extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    public function render(Varien_Object $row)
    {
        $html = parent::render($row);
        $format = ( $this->getColumn()->getFormat() ) ? $this->getColumn()->getFormat() : null;
        $_variablePattern = '/\\$([a-z0-9_]+)/i';
        if (empty($html) || is_null($format)) {
        } elseif (preg_match_all($_variablePattern, $format, $matches)) {
            $formattedString = $format;
            foreach ($matches[0] as $matchIndex=>$match) {
                $value = $row->getData($matches[1][$matchIndex]);
                $formattedString = str_replace($match, $value, $formattedString);
            }
            return $formattedString;
        } else {
            return $this->escapeHtml($format);
        }
        return $html;
    }
}