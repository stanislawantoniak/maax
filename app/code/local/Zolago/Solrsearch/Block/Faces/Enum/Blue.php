<?php
class Zolago_Solrsearch_Block_Faces_Enum_Blue
	extends Zolago_Solrsearch_Block_Faces_Enum_Abstract
{
	protected function _toHtml() {
		return  "<div style=\"background: #00FFFF;\">" . parent::_toHtml() . "</div>";
	}
}
