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
}
