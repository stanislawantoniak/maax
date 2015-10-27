<?php

class GH_Statements_Adminhtml_Vendor_BalanceController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('vendors/ghstatements');
        $this->_addContent($this->getLayout()->createBlock('ghstatements/adminhtml_vendor_balance'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('ghstatements/adminhtml_vendor_balance_grid')->toHtml()
        );
    }

}