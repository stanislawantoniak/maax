<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Gallery
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $out = '';
        $product = Mage::getModel('catalog/product')->load($row->getEntityId());
        $catalogHelper = $this->helper('catalog/image');

        $gallery = $product->getMediaGalleryImages();

        foreach ($gallery as $_image) {
            $thUrl = $catalogHelper->init($product, 'thumbnail', $_image->getFile())->resize(100);
            $img = "<div class='mass-thumb-image'><img src='" . $thUrl . "' /></div>";
            $out .= $img;
        }

        return $out;
    }
	
}