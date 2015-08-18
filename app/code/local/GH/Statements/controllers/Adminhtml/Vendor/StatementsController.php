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
        $id = $this->getRequest()->getParam('id');
        $statement = $this->_initModel($id);

        if(!$statement->getId()){
            $this->_getSession()->addError(Mage::helper('zolagocampaign')->__("Statement does not exists"));
            return $this->_redirect("*/*");
        }

        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return GH_Statements_Model_Statement
     */
    protected function _initModel($modelId) {
        if(Mage::registry('ghstatements_current_statement') instanceof GH_Statements_Model_Statement){
            return Mage::registry('ghstatements_current_statement');
        }

        $model = Mage::getModel("ghstatements/statement");
        /* @var $model GH_Statements_Model_Statement */
        if($modelId){
            $model->load($modelId);
        }

        Mage::register('ghstatements_current_statement', $model);
        return $model;
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

    /**
     * Manually trigger gh_statements cron with custom date (today)
     * @return Mage_Adminhtml_Controller_Action
     */
    public function generate_todayAction() {
        $forceCustomDate = $this->getRequest()->getParam('date');
        try {
            /** @var GH_Statements_Model_Observer $model */
            $model = Mage::getModel('ghstatements/observer');
            $model->processStatements(null,$forceCustomDate);
            return $this->_redirect("*/*/index");

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper("ghstatements")->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
    }
}
