<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelMicrosite") . DS . "Adminhtml" . DS . "RegistrationController.php";

/**
 * Class Zolago_DropshipMicrosite_Adminhtml_RegistrationController
 */
class Zolago_DropshipMicrosite_Adminhtml_RegistrationController extends ZolagoOs_OmniChannelMicrosite_Adminhtml_RegistrationController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/vendor_registration') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_registration');
    }
}
