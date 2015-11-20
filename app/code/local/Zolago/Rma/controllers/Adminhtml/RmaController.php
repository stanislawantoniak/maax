<?php

require_once Mage::getModuleDir('controllers', "Unirgy_Rma") . DS . "Adminhtml" . DS . "RmaController.php";

/**
 * Class Zolago_Rma_Adminhtml_RmaController
 */
class Zolago_Rma_Adminhtml_RmaController extends Unirgy_Rma_Adminhtml_RmaController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/urma') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/sales_management/urma');
    }
}