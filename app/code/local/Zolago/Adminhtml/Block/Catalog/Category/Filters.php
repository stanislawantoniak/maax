<?php
class Zolago_Adminhtml_Block_Catalog_Category_Filters extends Mage_Adminhtml_Block_Widget_Container
{
	protected function _prepareLayout() {
		$this->_addButton("back", array(
			"label" => Mage::helper('zolagoadminhtml')->__("Back"),
			"class" => "back",
			"onclick" => "setLocation('".$this->getUrl("*/catalog_category/edit", array("id"=>  $this->getCategory()->getId()))."')"
		));
		$this->_addButton("save", array(
			"label" => Mage::helper('zolagoadminhtml')->__("Save"),
			"class" => "save",
			"onclick" => "editForm.submit();"
		));
		return parent::_prepareLayout();
	}
	
	public function getSaveUrl() {
		return $this->getUrl("*/catalog_category_filters/save", array("_current"=>true));
	}
	
	public function getHeaderText() {
		return Mage::helper('zolagoadminhtml')->__("Edit filters for %s", 
			$this->htmlEscape($this->getCategory()->getName()));
	}
	
	/**
	 * @return Mage_Catalog_Model_Category
	 */
	public function getCategory(){
		return Mage::registry("current_category");
	}

}
