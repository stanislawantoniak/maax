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

        $productStatus = $product->getStatus();

        $gallery = $product->getFullMediaGalleryImages();

        if ($gallery->count() > 0) {

            $out .= "<ul class='vendor-image'>";
            foreach ($gallery as $_image) {
                $_file = $_image->getFile();

                $thUrl = $catalogHelper->init($product, 'thumbnail', $_file)->resize(100);
                $imageUrl = $catalogHelper->init($product, 'image', $_file)->resize(700);

                $valueId = $_image->getValueId();
                $productId = $product->getId();
                $productName = $product->getName();

                if ($_image['disabled']) {
                    $img = "<li data-productname='{$productName}' data-image='{$imageUrl}' data-product='{$productId}' data-value='{$valueId}' class='mass-thumb-image need-to-check'>
                    <div class='vendor-image-controls'>
                    <a class='vendor-image-availability' title='" . $_helper->__("This photo is currently disabled and you can not see them on your product page. You can enable on the picture by clicking on this button.") . "'>
                    <i class='icon-eye-close'></i>
                    </a>
                    <a class='vendor-image-zoom' title='" . $_helper->__("Zoom") . "'><i class='icon-zoom-in'></i></a>
                    <a class='vendor-image-delete' data-product='{$productId}' data-value='{$valueId}' title='" . $_helper->__("Delete") . "'><i class='icon-trash'></i></a>
                    </div>
                    <img style='min-height: 100px; min-width: 100px;' src='" . $thUrl . '?' . time() . "' />
                    <i class='icon icon-ban-circle'></i>
                    <div class='vendor-image-refresh'><i class='icon-spin icon-refresh'></i></div>
                    </li>";
                } else {
                    $img = "<li data-productname='{$productName}' data-image='{$imageUrl}' data-product='{$productId}' data-value='{$valueId}' class='mass-thumb-image'>
                    <div class='vendor-image-controls'>
                    <a class='vendor-image-availability' title='" . $_helper->__("This photo is currently enabled and you can see them on your product page. You can turn off the picture by clicking on this button.") . "'>
                    <i class='icon-eye-open'></i>
                    </a>
                    <a class='vendor-image-zoom' title='" . $_helper->__("Zoom") . "'><i class='icon-zoom-in'></i></a>
                    <a class='vendor-image-delete' data-value='{$valueId}' data-product='{$productId}' title='" . $_helper->__("Delete") . "'><i class='icon-trash'></i></a>
                    </div>
                    <img style='min-height: 100px; min-width: 100px;' src='{$thUrl}' />
                    <i class='icon icon-ban-circle' style='display: none'></i>
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