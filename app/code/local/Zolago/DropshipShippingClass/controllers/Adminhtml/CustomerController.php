<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelShippingClass") . DS . "Adminhtml" . DS . "CustomerController.php";

/**
 * Class Zolago_DropshipShippingClass_Adminhtml_CustomerController
 */
class Zolago_DropshipShippingClass_Adminhtml_CustomerController extends ZolagoOs_OmniChannelShippingClass_Adminhtml_CustomerController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udshipclass_customer') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/udshipclass_customer');
    }

}
