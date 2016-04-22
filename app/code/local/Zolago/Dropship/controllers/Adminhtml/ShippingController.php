<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannel") . DS . "Adminhtml" . DS . "ShippingController.php";

/**
 * Class ZolagoOs_OmniChannel_Adminhtml_ShippingController
 */
class Zolago_Dropship_Adminhtml_ShippingController extends ZolagoOs_OmniChannel_Adminhtml_ShippingController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/shipping') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_general_config/shipping');
    }

}
