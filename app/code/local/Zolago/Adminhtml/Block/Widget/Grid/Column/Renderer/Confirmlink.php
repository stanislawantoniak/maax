<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Confirmlink
    extends Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Link
{
	public function render(Varien_Object $row){
		$text = $this->getColumn()->getConfirmText() ? 
			$this->getColumn()->getConfirmText() : $this->__("Are you sure?");
		$label = $this->getColumn()->getLinkLabel();
		
		return '<a class="btn-notification" '.
			'data-placement="top" '.
			'data-ok-url="'.$this->_getLink($row).'" '.
			'data-layout="top" '.
			'data-type="confirm" '.
			'data-text="'.$this->escapeHtml($text).'" . '.
			'data-modal="true" data-status="<?php echo $key;?>" ' . 
			'href="#">' . $this->escapeHtml($label) . "</a>";
	}
}