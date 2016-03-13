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
        $productId = $row->getEntityId();
        $_helper = Mage::helper("zolagocatalog");

        $out .= "<div class='row'>";

        $out .= "<div class='col-md-10 col-lg-10 vendor-image-container no-padding'>";
        $out .= Mage::helper("zolagocatalog/image")->generateProductGallery($productId);

        $addImageLabel = $_helper->__("Add image");

        $out .= "</div>";
        $out .= "<div class='vendor-image-upload col-md-2 col-lg-2'>
                    <form>

                            <div class='btn btn-file' title='{$addImageLabel}'>
                                <i class='icon icon-plus-sign'></i> {$addImageLabel}
                                <input type='hidden' name='product' value='{$productId}' />
                                <input type='file' multiple name='vendor_image_upload[]' />
                            </div>

                    </form>
                </div>";

        $out .= "</div>";

        return $out;
    }

}