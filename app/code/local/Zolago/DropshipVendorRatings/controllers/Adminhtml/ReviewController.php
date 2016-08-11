<?php

require_once Mage::getModuleDir('controllers', "ZolagoOs_OmniChannelVendorRatings") . DS . "Adminhtml" . DS . "ReviewController.php";

/**
 * Class Zolago_DropshipVendorRatings_Adminhtml_ReviewController
 */
class Zolago_DropshipVendorRatings_Adminhtml_ReviewController extends ZolagoOs_OmniChannelVendorRatings_Adminhtml_ReviewController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return
                    Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review/review_pending') ||
                    Mage::getSingleton('admin/session')->isAllowed('admin/vendors/review/review_pending');
                break;
            default:
                return
                    Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review/review_all') ||
                    Mage::getSingleton('admin/session')->isAllowed('admin/vendors/review/review_all');
                break;
        }
    }
}
