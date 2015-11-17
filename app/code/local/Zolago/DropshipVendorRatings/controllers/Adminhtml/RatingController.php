<?php

require_once Mage::getModuleDir('controllers', "Unirgy_DropshipVendorRatings") . DS . "Adminhtml" . DS . "RatingController.php";

/**
 * Class Zolago_DropshipVendorRatings_Adminhtml_RatingController
 */
class Zolago_DropshipVendorRatings_Adminhtml_RatingController extends Unirgy_DropshipVendorRatings_Adminhtml_RatingController {

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return
            Mage::getSingleton('admin/session')->isAllowed('sales/udropship/review/rating') ||
            Mage::getSingleton('admin/session')->isAllowed('admin/vendors/review/rating');
    }

}
