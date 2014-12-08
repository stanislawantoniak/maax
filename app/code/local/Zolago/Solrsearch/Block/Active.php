<?php

class Zolago_Solrsearch_Block_Active extends Zolago_Solrsearch_Block_Faces
{
	public function _construct(){
		parent::_construct();
		$this->setTemplate("zolagosolrsearch/standard/active.phtml");
	}
	
	public function _prepareLayout() {
		$this->setSkip(1);
		return parent::_prepareLayout();
	}

	public function getItemId($attributeCode, $item) {
		if($attributeCode=="price" && $this->getRequest()->getParam('slider')){
			return "filter_slider";
		}
		return parent::getItemId($attributeCode, $item);
	}
	
}