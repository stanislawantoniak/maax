<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelPo") . DS . "Adminhtml" . DS . "PoController.php";

/**
 * Class Zolago_Po_Adminhtml_PoController
 */
class Zolago_Po_Adminhtml_PoController extends ZolagoOs_OmniChannelPo_Adminhtml_PoController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udpo') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/sales_management/udpo');
    }

}