<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_Rma") . DS . "Adminhtml" . DS . "Order" .DS . "RmaController.php";

/**
 * Class Zolago_Rma_Adminhtml_Order_RmaController
 */
class Zolago_Rma_Adminhtml_Order_RmaController extends ZolagoOs_Rma_Adminhtml_Order_RmaController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return (Mage::getSingleton('admin/session')->isAllowed('sales/udropship/urma') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/sales_management/urma'))
        && (
            !in_array($this->getRequest()->getActionName(), array('new', 'save'))
            || Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/urma')
        );
    }
}
