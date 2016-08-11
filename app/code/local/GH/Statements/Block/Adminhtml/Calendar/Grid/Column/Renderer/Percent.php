<?php

class GH_Statements_Block_Adminhtml_Calendar_Grid_Column_Renderer_Percent
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Number
{
	/**
	 * Returns value of the row
	 *
	 * @param Varien_Object $row
	 * @return mixed|string
	 */
	protected function _getValue(Varien_Object $row)
	{
		$data = parent::_getValue($row);
		if (!is_null($data)) {
			$value = $data * 1;
			$sign = (bool)(int)$this->getColumn()->getShowNumberSign() && ($value > 0) ? '+' : '';
			if ($sign) {
				$value = $sign . $value;
			}
			return $value . "%" ? $value. "%" : '0%'; // fixed for showing zero in grid
		}
		return $this->getColumn()->getDefault();
	}
	
	
}