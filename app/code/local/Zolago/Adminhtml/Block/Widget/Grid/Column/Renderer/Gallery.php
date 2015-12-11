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

                if ($_image['disabled']) {
                    $img = "<li class='mass-thumb-image need-to-check'><img src='" . $thUrl . '?' . time() . "' /></li>";
                } else {
                    $img = "<li class='mass-thumb-image'><img src='" . $thUrl . "' /></li>";
                }
                $out .= $img;


            }
            $out .= "</ul>";
        }
        return $out;
    }
	
}