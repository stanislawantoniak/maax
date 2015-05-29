<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Gallery
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $out = '';
        $product = Mage::getModel('zolagocatalog/product')->load($row->getEntityId());
        $catalogHelper = $this->helper('catalog/image');

        $gallery = $product->getFullMediaGalleryImages();

        if ($gallery->count() > 0) {
            foreach ($gallery as $_image) {
                $thUrl = $catalogHelper->init($product, 'thumbnail', $_image->getFile())->resize(100);

                if($_image['disabled']) {
                    $img = "<div class='mass-thumb-image need-to-check'><img src='" . $thUrl .'?'.time(). "' /></div>";
                } else {
                    $img = "<div class='mass-thumb-image'><img src='" . $thUrl . "' /></div>";
                }

                $out .= $img;
            }
        }
        return $out;
    }
	
}