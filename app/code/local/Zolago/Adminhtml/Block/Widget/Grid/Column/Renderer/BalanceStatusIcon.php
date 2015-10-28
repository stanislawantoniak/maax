<?php

class Zolago_Adminhtml_Block_Widget_Grid_Column_Renderer_BalanceStatusIcon
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        $status = (int) $row->getData('status');
        /** @var GH_Statements_Helper_Data $helper */
        $helper = Mage::helper('ghstatements');
        if ($status) {
            return $helper->__("Close") .  " <i class='icon-eye-close'></i>";
        } else {
            return $helper->__("Open") .  " <i class='icon-eye-open'></i>";
        }
    }
}