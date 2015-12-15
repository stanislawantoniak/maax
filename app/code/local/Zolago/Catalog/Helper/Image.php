<?php

class Zolago_Catalog_Helper_Image extends Mage_Catalog_Helper_Product
{

    /**
     * @param $productId
     * @return string
     */
    public function generateProductGallery($productId)
    {
        $out = '';
        $product = Mage::getModel('zolagocatalog/product')->load($productId);
        $catalogHelper = Mage::helper('catalog/image');
        $_helper = Mage::helper("zolagocatalog");

        $gallery = $product->getFullMediaGalleryImages();

        if ($gallery->count() > 0) {
            $out .= "<div class='col-md-11 vendor-image-container no-padding'>";
            $out .= "<ul class='vendor-image'>";
            foreach ($gallery as $_image) {
                $_file = $_image->getFile();
                $imageUrl = $_image->getUrl();
                $thUrl = $catalogHelper->init($product, 'thumbnail', $_file)->resize(100);

                $valueId = $_image->getValueId();
                $productId = $product->getId();
                $productName = $product->getName();

                if ($_image['disabled']) {
                    $img = "<li data-productname='{$productName}' data-image='{$imageUrl}' data-product='{$productId}' data-value='{$valueId}' class='mass-thumb-image need-to-check'>
                    <div class='vendor-image-controls'>
                    <a class='vendor-image-availability' title='" . $_helper->__("Enable") . "'><i class='icon-circle'></i></a>
                    <a class='vendor-image-zoom' title='" . $_helper->__("Zoom") . "'><i class='icon-zoom-in'></i></a>
                    <a class='vendor-image-delete' data-value='{$valueId}' title='" . $_helper->__("Delete") . "'><i class='icon-trash'></i></a>
                    </div>
                    <img src='" . $thUrl . '?' . time() . "' />
                    <div class='vendor-image-refresh'><i class='icon-spin icon-refresh'></i></div>
                    </li>";
                } else {
                    $img = "<li data-productname='{$productName}' data-image='{$imageUrl}' data-product='{$productId}' data-value='{$valueId}' class='mass-thumb-image'>
                    <div class='vendor-image-controls'>
                    <a class='vendor-image-availability' title='" . $_helper->__("Disable") . "'><i class='icon-ban-circle'></i></a>
                    <a class='vendor-image-zoom' title='" . $_helper->__("Zoom") . "'><i class='icon-zoom-in'></i></a>
                    <a class='vendor-image-delete' data-value='{$valueId}' data-product='{$productId}' title='" . $_helper->__("Delete") . "'><i class='icon-trash'></i></a>
                    </div>
                    <img src='{$thUrl}' />
                    <div class='vendor-image-refresh'><i class='icon-spin icon-refresh'></i></div>
                    </li>";
                }
                $out .= $img;
            }
            $out .= "</ul>";
            $out .= "</div>";
        }

        return $out;
    }
}