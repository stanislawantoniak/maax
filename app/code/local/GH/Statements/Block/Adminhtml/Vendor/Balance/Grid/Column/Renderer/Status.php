<?php

class GH_Statements_Block_Adminhtml_Vendor_Balance_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
	/**
	 * Render a grid cell as options
	 *
	 * @param Varien_Object $row
	 * @return string
	 */
	public function render(Varien_Object $row)
	{
		$options = $this->getColumn()->getOptions();
		$showMissingOptionValues = (bool)$this->getColumn()->getShowMissingOptionValues();
		if (!empty($options) && is_array($options)) {
			$value = $row->getData($this->getColumn()->getIndex());

			if ($value == 1) {
				$style = "style='background-color: red;color: white;padding: 0 25%;font-weight: bold;'";
			} else {
				$style = "style='background-color: green;color: white;padding: 0 25%;font-weight: bold;'";
			}

			if (is_array($value)) {
				$res = array();
				foreach ($value as $item) {
					if (isset($options[$item])) {
						$res[] = $this->escapeHtml($options[$item]);
					}
					elseif ($showMissingOptionValues) {
						$res[] = $this->escapeHtml($item);
					}
				}
				return implode(', ', $res);
			} elseif (isset($options[$value])) {
				return "<div {$style}>".$this->escapeHtml($options[$value])."</div>";
			} elseif (in_array($value, $options)) {
				return "<div {$style}>".$this->escapeHtml($value)."</div>";
			}
		}
	}
}