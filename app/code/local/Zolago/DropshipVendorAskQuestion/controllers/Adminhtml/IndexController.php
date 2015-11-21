<?php

require_once Mage::getModuleDir('controllers', "Unirgy_DropshipVendorAskQuestion") . DS . "Adminhtml" . DS . "IndexController.php";

/**
 * Class Unirgy_DropshipVendorAskQuestion_Adminhtml_IndexController
 */
class Zolago_DropshipVendorAskQuestion_Adminhtml_IndexController extends Unirgy_DropshipVendorAskQuestion_Adminhtml_IndexController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        switch ($this->getRequest()->getActionName()) {
            case 'pending':
                return
                    Mage::getSingleton('admin/session')->isAllowed('sales/udropship/question/question_pending') ||
                    Mage::getSingleton('admin/session')->isAllowed('admin/vendors/udqa/question_pending');
                break;
            default:
                return
                    Mage::getSingleton('admin/session')->isAllowed('sales/udropship/question/question_all') ||
                    Mage::getSingleton('admin/session')->isAllowed('admin/vendors/udqa/question_all');
                break;
        }
    }
}
