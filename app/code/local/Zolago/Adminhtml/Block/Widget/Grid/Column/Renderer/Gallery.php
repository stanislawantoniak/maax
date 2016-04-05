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

        $out .= "<div style='display:table'><div style='display:table-row'>";

        $out .= "<div class='vendor-image-container' style='display:table-cell'>";
        $out .= Mage::helper("zolagocatalog/image")->generateProductGallery($productId);

        $addImageLabel = $_helper->__("Add image");

        $out .= "</div>";
        $out .= "<div class='vendor-image-upload' style='display:table-cell;vertical-align:top'>
                    <form>
                        <input type='hidden' name='product' value='{$productId}' style='cursor:pointer' />
                        <label class='btn btn-file' title='{$addImageLabel}' style='cursor:pointer !important;'>
                            <i class='icon icon-plus-sign'></i> {$addImageLabel}
                            <input type='file' multiple name='vendor_image_upload[]' />
                        </label>
                    </form>
                </div>";

        $out .= "</div></div>";

        return $out;
    }

}