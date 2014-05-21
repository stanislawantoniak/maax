<?php
// grid column widget with coloured background

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Options extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
    public function render(Varien_Object $row) {
        $options = $this->getColumn()->getOptions();
        $styles = $this->getColumn()->getStyle();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
            $value = $row->getData($this->getColumn()->getIndex());
            if (is_array($value)) {
                $res = array();
                foreach ($value as $item) {
                    if (isset($options[$item])) {
                        $res[] = '<span style="'.$styles[$item].'">'.$this->escapeHtml($options[$item]).'</span>';
                    }
                    elseif ($showMissingOptionValues) {
                        $res[] = $this->escapeHtml($item);
                    }
                }
                return implode(', ', $res);
            }
            elseif (isset($options[$value])) {
                return '<span style="'.$styles[$value].'">'.$this->escapeHtml($options[$value]).'</span>';
            }
            elseif (in_array($value, $options)) {
                return $this->escapeHtml($value);
            }
        }
    }

}