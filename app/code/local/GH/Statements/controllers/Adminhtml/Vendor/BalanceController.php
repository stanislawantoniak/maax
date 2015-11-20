<?php

/**
 * Class GH_Statements_Adminhtml_Vendor_BalanceController
 */
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

    /**
     * funkcja zamykania miesiąca
     */
    public function closeMonthAction()
    {
        $id = $this->getRequest()->getParam("id");

        try {
            $model = Mage::getModel("ghstatements/vendor_balance")->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('ghstatements')->__("Row not found"));
            }
            $model->setStatus(GH_Statements_Model_Vendor_Balance::GH_VENDOR_BALANCE_STATUS_CLOSED);
            $model->save();
            $this->_getSession()->addSuccess(Mage::helper('ghstatements')->__("Status changed"));
        } catch (GH_Common_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('ghstatements')->__("Some error occurred!"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*");
    }

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/ghstatements_balance');
    }
}