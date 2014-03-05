<?php
class Zolago_Adminhtml_Block_Catalog_Category_Edit_Form extends Mage_Adminhtml_Block_Catalog_Category_Edit_Form
{
	
	protected function _prepareLayout() {
		
		parent::_prepareLayout();
		if($this->getCategory()->getId()){
			$filterUrl  =$this->getUrl("*/catalog_category_filters/edit", 
					array("category_id"=>  $this->getCategory()->getId()));
			
			$this->addAdditionalButton("filters", array(
				"label" => Mage::helper('zolagoadminhtml')->__("Custom filters"),
				"id"	=> 'filters',
				"onclick" => "setLocation('".$filterUrl."');"
			));
		}
		return $this;
	}


}
