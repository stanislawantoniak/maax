<?php

require_once Mage::getModuleDir('controllers', "Unirgy_DropshipMicrosite") . DS . "Adminhtml" . DS . "RegistrationController.php";

/**
 * Class Zolago_DropshipMicrosite_Adminhtml_RegistrationController
 */
class Zolago_DropshipMicrosite_Adminhtml_RegistrationController extends Unirgy_DropshipMicrosite_Adminhtml_RegistrationController {

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
