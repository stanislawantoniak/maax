<?php

/**
 * Customer admin controller
 */
class Zolago_Adminhtml_Customer_ActivityController extends Mage_Adminhtml_Controller_Action {

    /**
     * Show grid with login/logout data for customer
     */
    public function loginAction() {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock("zolagoadminhtml/customer_activity_login_grid", "admin.customer.activity.login.grid")
                ->setCustomerId($this->getRequest()->getParam('id'))
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    /**
     * Show grid with recently viewed products for customer
     */
    public function recentlyviewedAction() {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock("zolagoadminhtml/customer_activity_recentlyviewed_grid", "admin.customer.activity.recentlyviewed.grid")
                ->setCustomerId($this->getRequest()->getParam('id'))
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    /**
     * Show grid with viewed categories (pages)
     */
    public function viewedcategoriesAction() {
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock("zolagoadminhtml/customer_activity_viewedcategories_grid", "admin.customer.activity.viewedcategories.grid")
                ->setCustomerId($this->getRequest()->getParam('id'))
                ->setUseAjax(true)
                ->toHtml()
        );
    }

    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('customer/manage');
    }
}
