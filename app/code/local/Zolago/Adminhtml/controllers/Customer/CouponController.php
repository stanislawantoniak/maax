<?php

/**
 * Admin controller for customers coupons tab
 *
 * Class Zolago_Adminhtml_Customer_CouponController
 */
class Zolago_Adminhtml_Customer_CouponController extends Mage_Adminhtml_Controller_Action {

    /**
     * Show grid with coupons data
     */
    public function indexAction() {
        /** @var Zolago_Adminhtml_Block_Customer_Beacon_Data_Grid $block */
        $block = $this->getLayout()->createBlock("zolagoadminhtml/customer_coupon_grid", "admin.customer.coupon.grid");
        $block->setCustomerId($this->getRequest()->getParam('id'))
            ->setUseAjax(true);
        $this->getResponse()->setBody($block->toHtml());
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
