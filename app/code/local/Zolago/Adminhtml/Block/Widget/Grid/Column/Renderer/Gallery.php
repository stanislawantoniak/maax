<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Gallery
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	
	public function render(Varien_Object $row){
	    $out = '';
	    $product = Mage::getModel('catalog/product')->load($row->getEntityId());	    
        $media = $product->getMediaGallery('images');
	    if ($media) {
	        $counter = 0;
    	    foreach ($media as $image) {
	            $url = $product->getMediaConfig()->getMediaUrl($image['file']);
    			$img = "<div style=\"width:100px;height:100px;text-align:center;margin:2px;float:left;\"><img src=\"$url\" alt=\"\" style=\"max-width:100px;max-height:100px\"/></div>";
    			$out .= $img;                
    			$counter ++;
    			if ($counter >5) {
    			    $counter = 0;
    			    $out .= '<div style="clear:both"></div>';
    			}
	        }
	    }
	    return $out;
		$src = $this->_getThumbUrl($row);
		if($src){
			$img = "<img src=\"$src\" alt=\"\"/>";
			if($this->getColumn()->getClickable()){
				return "<a href=\"{$this->_getImageUrl($row)}\" target=\"_blank\">$img</a>";
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