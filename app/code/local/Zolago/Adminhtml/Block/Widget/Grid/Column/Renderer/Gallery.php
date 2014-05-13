<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Gallery
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
	
	public function render(Varien_Object $row){
	    $out = '';
	    $product = Mage::getModel('catalog/product')->load($row->getEntityId());	    
        $media = $product->getMediaGallery('images');
	    if ($media) {
    	    foreach ($media as $image) {
	            $url = $product->getMediaConfig()->getMediaUrl($image['file']);
    			$img = "<div style=\"width:100px;height:100px;text-align:center;margin:2px;float:left;\"><img src=\"$url\" alt=\"\" style=\"max-width:100px;max-height:100px\"/></div>";
    			$out .= $img;                
	        }
	    }
	    return $out;
	}
	
	
}