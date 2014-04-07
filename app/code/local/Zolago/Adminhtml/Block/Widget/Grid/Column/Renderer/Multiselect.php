<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Multiselect
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	
	public function render(Varien_Object $row){
		$options = $this->getColumn()->getOptions();
        $showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
        if (!empty($options) && is_array($options)) {
			$res = array();
			foreach ($this->_getValue($row) as $item) {
				if (isset($options[$item])) {
					$res[] = $this->escapeHtml($options[$item]);
				}
				elseif ($showMissingOptionValues) {
					$res[] = $this->escapeHtml($item);
				}
			}
			return implode(', ', $res);

        }
	}
	
	public function _getValue(Varien_Object $row){
		$value = $row->getData($this->getColumn()->getIndex());
		if(!is_array($value)){
				$value = explode(",", $value);
		}
		return $value;
	}
}