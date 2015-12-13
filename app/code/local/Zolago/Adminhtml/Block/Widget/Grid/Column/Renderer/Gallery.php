<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_Gallery
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    /**
     * @param Varien_Object $row
     * @return string
     */
    public function render(Varien_Object $row)
    {
        $out = '';
        $product = Mage::getModel('zolagocatalog/product')->load($row->getEntityId());
        $catalogHelper = $this->helper('catalog/image');

        $gallery = $product->getFullMediaGalleryImages();

        if ($gallery->count() > 0) {
            $out .= "<ul class='vendor-image'>";
            foreach ($gallery as $_image) {

                $thUrl = $catalogHelper->init($product, 'thumbnail', $_image->getFile())->resize(100);

                $valueId = $_image->getValueId();
                $productId = $product->getId();

                if ($_image['disabled']) {
                    $img = "<li data-product='".$productId."' data-value='".$valueId."' class='mass-thumb-image need-to-check'><img src='" . $thUrl . '?' . time() . "' /></li>";
                } else {
                    $img = "<li data-product='".$productId."' data-value='".$valueId."' class='mass-thumb-image'>
                    <div class='vendor-image-controls'><a title='".$this->__("Turn off")."'><i class='icon-ban-circle'></i></a></div>
                    <img src='" . $thUrl . "' /></li>";
                }
                $out .= $img;


            }
            $out .= "</ul>";
        }
        return $out;
    }
	
}