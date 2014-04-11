<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Status
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Options
{
	
	public function render(Varien_Object $row){
		switch ($row->getData($this->getColumn()->getIndex())){
			case Mage_Catalog_Model_Product_Status::STATUS_ENABLED:
				return Mage::helper('zolagocatalog')->__("On");
			break;
			case Mage_Catalog_Model_Product_Status::STATUS_DISABLED:
				return Mage::helper('zolagocatalog')->__("Off");
			break;
			default:
				return Mage::helper('zolagocatalog')->__("Wait");
			break;
		}
	}
}