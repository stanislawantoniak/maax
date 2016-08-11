<?php

class Zolago_Mapper_Block_Adminhtml_Mapper_Grid_Column_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
	/**
	 * @param Varien_Object $row
	 * @return string
	 */
    public function render(Varien_Object $row){
		$urls = array();
		if($row->getId()){
			$urls[] = array(
				"label"=>Mage::helper('zolagomapper')->__('View'),
				"url" => $this->getUrl('*/*/edit', array("mapper_id"=>$row->getId()))
			);
			$urls[] = array(
				"label"=>Mage::helper('zolagomapper')->__('Run'),
				"url" => $this->getUrl('*/*/run', array("back"=>"list", "mapper_id"=>$row->getId()))
			);
            $urls[] = array(
                "label"=>Mage::helper('zolagomapper')->__('Add to queue'),
                "url" => $this->getUrl('*/*/queue', array("mapper_id"=>$row->getId()))
            );
		}else{
			$urls[] = array(
				"label" => Mage::helper('zolagomapper')->__('Create'),
				"url"=> $this->getUrl('*/*/new', array("back"=>"list"))
			);
		}
		$toImplode = array();
		foreach ($urls as $url){
			$toImplode[] = '<a href="'.$url['url'].'">'.$this->escapeHtml($url['label']).'</a>';
		}
		return implode(" | ", $toImplode);
		
	}
	
	
}