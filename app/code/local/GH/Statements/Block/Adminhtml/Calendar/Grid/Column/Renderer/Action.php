<?php

class GH_Statements_Block_Adminhtml_Calendar_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
    public function render(Varien_Object $row){
		$urls = array();
		$urls[] = array(
			"label"=>Mage::helper('zolagomapper')->__('Edit'),
			"url" => $this->getUrl('*/*/calendar_edit', array("id"=>$row->getId()))
		);
		$urls[] = array(
			"label"=>Mage::helper('zolagomapper')->__('Edit events'),
			"url" => $this->getUrl('*/*/calendar_item', array("id"=>$row->getId()))
		);
		$toImplode = array();
		foreach ($urls as $url){
			$toImplode[] = '<a href="'.$url['url'].'">'.$this->escapeHtml($url['label']).'</a>';
		}
		return implode(" | ", $toImplode);
		
	}
	
	
}