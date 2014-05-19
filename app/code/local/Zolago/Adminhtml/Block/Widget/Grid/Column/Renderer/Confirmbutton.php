<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Confirmbutton
    extends Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Link
{
	public function render(Varien_Object $row){
		$text = $this->getColumn()->getConfirmText() ? 
			$this->getColumn()->getConfirmText() : $this->__("Are you sure?");
		$label = $this->getColumn()->getLinkLabel();
		
		return '<button class="btn-notification btn-xs '.$this->getColumn()->getClass().'" '.
			'data-placement="top" '.
			'data-ok-url="'.$this->_getLink($row).'" '.
			'data-layout="top" '.
			'data-type="confirm" '.
			'data-text="'.$this->escapeHtml($text).'" . '.
			'data-modal="true">'. 
				($this->getColumn()->getIcon() ? 
				'<i class="'.$this->getColumn()->getIcon().'"></i> ' : '' ) . 
				$this->escapeHtml($label) . 
			'</button>';
	}
}