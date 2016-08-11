<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelPo") . DS . "Adminhtml" . DS . "Order" .DS . "PoController.php";

/**
 * Class Zolago_Po_Adminhtml_Order_PoController
 */
class Zolago_Po_Adminhtml_Order_PoController extends ZolagoOs_OmniChannelPo_Adminhtml_Order_PoController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return (Mage::getSingleton('admin/session')->isAllowed('sales/udropship/udpo') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/sales_management/udpo'))
        && (
            !in_array($this->getRequest()->getActionName(), array('editCosts', 'saveCosts'))
            || Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/udpo_edit_cost')
        );
    }
}
