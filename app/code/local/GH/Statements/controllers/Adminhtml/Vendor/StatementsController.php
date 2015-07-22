<?php

class GH_Statements_Adminhtml_Vendor_StatementsController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function editAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /*
     * orderGrid ajax block response
     */
    public function orderGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_order',
                    'ghstatements.statement.order'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

    /**
     * refundGrid ajax block response
     */
    public function refundGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_refunds',
                    'ghstatements.statement.refunds'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

}
