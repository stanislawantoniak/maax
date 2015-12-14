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
        $_helper = Mage::helper("zolagocatalog");

        $gallery = $product->getFullMediaGalleryImages();

        if ($gallery->count() > 0) {
            $out .= "<ul class='vendor-image'>";
            foreach ($gallery as $_image) {
                $_file = $_image->getFile();

                $thUrl = $catalogHelper->init($product, 'thumbnail', $_file)->resize(100);

                $valueId = $_image->getValueId();
                $productId = $product->getId();
                $productName = $product->getName();

                $_fileUrl = Mage::getBaseUrl("media") . DS . "catalog" . DS . "product" . $_file;

                if ($_image['disabled']) {
                    $img = "<li data-productname='{$productName}' data-image='{$_fileUrl}' data-product='{$productId}' data-value='{$valueId}' class='mass-thumb-image need-to-check'>
                    <div class='vendor-image-controls'>
                    <a class='vendor-image-availability' title='" . $_helper->__("Enable") . "'><i class='icon-circle'></i></a>
                    <a class='vendor-image-zoom' title='" . $_helper->__("Zoom") . "'><i class='icon-zoom-in'></i></a>
                    </div>
                    <img src='" . $thUrl . '?' . time() . "' />
                    <div class='vendor-image-refresh'><i class='icon-spin icon-refresh'></i></div>
                    </li>";
                } else {
                    $img = "<li data-productname='{$productName}' data-image='{$_fileUrl}' data-product='{$productId}' data-value='{$valueId}' class='mass-thumb-image'>
                    <div class='vendor-image-controls'>
                    <a class='vendor-image-availability' title='" . $_helper->__("Disable") . "'><i class='icon-ban-circle'></i></a>
                    <a class='vendor-image-zoom' title='" . $_helper->__("Zoom") . "'><i class='icon-zoom-in'></i></a>
                    </div>
                    <img src='{$thUrl}' />
                    <div class='vendor-image-refresh'><i class='icon-spin icon-refresh'></i></div>
                    </li>";
                }
                $out .= $img;
            }
            $out .= "</ul>";
        }
        return $out;
    }

}