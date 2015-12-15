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

        $out .= Mage::helper("zolagocatalog/image")->generateProductGallery($productId);
        $out .= "<div class='vendor-image-upload col-md-1 no-padding'>
                    <form>
                        <span class='btn-file' title='" . $_helper->__("Upload image") . "'>
                            <i class='icon icon-plus-sign'></i>
                            <input type='hidden' name='product' value='{$productId}' />
                            <input type='file' name='vendor_image_upload' />
                        </span>
                    </form>
                </div>";
        return $out;
    }

}