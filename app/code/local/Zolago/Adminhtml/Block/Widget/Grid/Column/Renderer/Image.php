<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Image
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	
	public function render(Varien_Object $row){
		$src = $this->_getThumbUrl($row);
		if($src){
			$img = "<img src=\"$src\" alt=\"\"/>";
			if($this->getColumn()->getClickable()){
				return "<a href=\"{$this->_getImageUrl($row)}\" target=\"blank\">$img</a>";
			}
			return $img;
		}
		return "";
	}
	
	protected function _getImageUrl(Varien_Object $row) {
		return Mage::getBaseUrl("media") . "catalog/product" . $this->_getValue($row); 
	}
	
	protected function _validImage($row) {
		$value = $this->_getValue($row);
		return  $value!="no_selection" && 
				!empty($value) && 
				file_exists(Mage::getBaseDir("media") . DS ."catalog" . DS . "product"  . $value);
	}


	protected function _getThumbUrl(Varien_Object $row) {
		if($this->getColumn()->getAttribute() && $this->_validImage($row)){
			return Mage::helper('catalog/image')->
			   init($row, $this->getColumn()->getAttribute()->getAttributeCode())->
			   resize(100, 100);
		}
		return null;
	}
	
}