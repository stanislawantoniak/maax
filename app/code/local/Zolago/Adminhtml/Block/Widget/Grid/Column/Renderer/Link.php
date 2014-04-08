<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Link
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	
	public function render(Varien_Object $row){
		return "<a href=\"{$this->_getLink($row)}\" target=\"blank\">{$this->getColumn()->getLinkLabel()}</a>";
	}
	
	protected function _getLink(Varien_Object $row) {
		return Mage::getUrl(
				$this->getColumn()->getLinkAction(), 
				array($this->getColumn()->getLinkParam()=>$this->_getValue($row))
		);
	}
}