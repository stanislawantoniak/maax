<?php

class GH_Statements_Block_Dropship_Balance_Grid_Column_Renderer_BalanceStatusIcon
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row) {
        $status = (int) $row->getData('status');
        /** @var GH_Statements_Helper_Data $helper */
        $helper = Mage::helper('ghstatements');
        if ($status == GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_CLOSED) {
            return  "<i class='icon-lock'></i> ".$helper->__("Close");
        } else {
            return  "<i class='icon-unlock'></i> ".$helper->__("Open");
        }
    }
}