<?php
/**
 * renderer yes/no display
 */
class Zolago_Dropship_Block_Adminhtml_Vendor_Brandshop_Renderer extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {
    public function render(Varien_Object $row) {
        $out = $this->_getValue($row);
        return $out? Mage::helper('zolagodropship')->__('Yes'):
            Mage::helper('zolagodropship')->__('No');
    }
}