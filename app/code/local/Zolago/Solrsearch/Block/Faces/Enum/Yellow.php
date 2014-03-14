<?php
class Zolago_Solrsearch_Block_Faces_Enum_Yellow extends Zolago_Solrsearch_Block_Faces_Enum_Abstract
{
	protected function _toHtml() {
		return  "<div style=\"background: #FFFF00;\">" . parent::_toHtml() . "</div>";
	}
}
