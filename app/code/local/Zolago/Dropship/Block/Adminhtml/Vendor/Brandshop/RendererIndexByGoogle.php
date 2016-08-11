<?php

/**
 * renderer yes(default use config)/no display
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Brandshop_RendererIndexByGoogle extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $out = $this->_getValue($row);
        $indexByGoogleOptions = Mage::getSingleton('zolagodropship/source')
            ->setPath('vendorindexbygoogle')
            ->toOptionHash();
        return $indexByGoogleOptions[(int)$out];
    }
}