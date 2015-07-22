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
    public function deleteAction()
    {
        $model = Mage::getModel("ghstatements/statement");
        $id = $this->getRequest()->getParam("id");

        try {
            $model->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('ghstatements')->__("Statement not found"));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('ghstatements')->__("Statement deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirect("*/*/edit",array('id' => $id));
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('ghstatements')->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirect("*/*/edit",array('id' => $id));
        }

        return $this->_redirect("*/*/index");
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

    /**
     * trackGrid ajax block response
     */
    public function trackGridAction()
    {
        $this->getResponse()->setBody(
            $this->getLayout()
                ->createBlock(
                    'ghstatements/adminhtml_vendor_statements_edit_tab_track',
                    'ghstatements.statement.track'
                )
                ->setStatementId($this->getRequest()->getParam('id'))
                ->toHtml()
        );
    }

}
