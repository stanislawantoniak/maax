<?php

class Zolago_Po_Block_Vendor_Aggregated_Grid_Column_Renderer_Confirmbutton
    extends Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Confirmbutton
{
	public function render(Varien_Object $row){
		if($row->getStatus()!=Zolago_Po_Model_Aggregated_Status::STATUS_CONFIRMED){
			if (Orba_Shipping_Model_Post::CODE == $row->getCarrier()->getCode()) {
				return $this->renderPost($row);
			}
			return parent::render($row);
		}
		return '';
	}
	
    /**
     * rendering for poczta polska
     */
     public function renderPost($row) {
     		$label = Mage::helper('orbashipping')->__('Send post envelope');
		$return = '<button class="btn-xs '.$this->getColumn()->getClass().'" '.
			'data-toggle="modal" '.
			'data-target="#modal_post_aggregate" '.
			'data-id="'.$row->getId().'" '.
			'>'. 
				($this->getColumn()->getIcon() ? 
				'<i class="'.$this->getColumn()->getIcon().'"></i> ' : '' ) . 
				$this->escapeHtml($label) . 
			'</button>';
		return $return;
     
     }
}