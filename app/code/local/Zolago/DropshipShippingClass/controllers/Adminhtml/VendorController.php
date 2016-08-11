<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelShippingClass") . DS . "Adminhtml" . DS . "VendorController.php";

/**
 * Class Zolago_DropshipShippingClass_Adminhtml_VendorController
 */
class Zolago_DropshipShippingClass_Adminhtml_VendorController extends ZolagoOs_OmniChannelShippingClass_Adminhtml_VendorController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udshipclass_vendor') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/udshipclass_vendor');
    }
}
