<?php

/**
 * Class Zolago_Payment_Adminhtml_Vendor_InvoiceController
 */
class Zolago_Payment_Adminhtml_Vendor_InvoiceController extends Mage_Adminhtml_Controller_Action
{
	protected $_helper;

    /**
     * Vendor Invoice Grid
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return Mage_Adminhtml_Controller_Action
     */
    public function editAction()
    {
        $id = $this->getRequest()->getParam("id");

        try {
            $model = Mage::getModel("zolagopayment/vendor_invoice")->load($id);
            if (!$model->getId()) {
                // Default values for form
                $model->setDefaults();
            }
            $sessionData = $this->_getSession()->getFormData();
            if (!empty($sessionData)) {
                $model->addData($sessionData);
                $this->_getSession()->setFormData(null);
            }
            Mage::register("zolagopayment_current_invoice", $model);
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper("zolagopayment")->__("Some error occurred!"));
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        $this->loadLayout();
        $this->renderLayout();
    }

    /**
     * @return $this|Mage_Adminhtml_Controller_Action|Mage_Core_Controller_Varien_Action
     */
    public function saveAction()
    {
        $model = Mage::getModel("zolagopayment/vendor_invoice");
        $helper = Mage::helper('zolagopayment');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("id");

        $this->_getSession()->setFormData(null);

        try {
            if ($this->getRequest()->isPost()) {
                $model->load($modelId);
	            if(isset($data['note']) && $data['note']) {
		            $data['note'] = substr($data['note'], 0, GH_Wfirma_Model_Client::NOTE_FIELD_LENGTH);
	            }
                $model->addData($data);
                $validErrors = $model->validate();

                if ($validErrors === true) {
                    $model->save();
                } else {
                    $this->_getSession()->setFormData($data);
                    foreach ($validErrors as $error) {
                        $this->_getSession()->addError($error);
                    }
                    return $this->_redirectReferer();
                }
                $this->_getSession()->addSuccess($helper->__("Vendor Invoice Saved"));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_getSession()->setFormData($data);
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError($helper->__("Some error occurred!"));
            $this->_getSession()->setFormData($data);
            Mage::logException($e);
            return $this->_redirectReferer();
        }
        return $this->_redirect("*/*");
    }

    public function deleteAction()
    {
        $id = $this->getRequest()->getParam("id");

        try {
            $model = Mage::getModel("zolagopayment/vendor_invoice")->load($id);
            if (!$model->getId()) {
	            Mage::throwException($this->_getHelper()->__("Vendor Invoice not found"));
            }
	        if($model->getData('wfirma_invoice_id')) {
		        Mage::throwException($this->_getHelper()->__("Can't delete. Invoice already generated in  wFirma system"));
	        }
            if ($model->getStatementId()) {
	            Mage::throwException($this->_getHelper()->__("Can't delete. Invoice in statement"));
            }
            $model->delete();
            $this->_getSession()->addSuccess($this->_getHelper()->__("Vendor Invoice Deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->_getHelper()->__("Some error occurred!"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*");
    }

    public function generateAction()
    {
        $id = $this->getRequest()->getParam("id");
        /** @var GH_Wfirma_Helper_Data $wfirmaHlp */
        $wfirmaHlp = Mage::helper('ghwfirma');
        try {
            /** @var Zolago_Payment_Model_Vendor_Invoice $model */
            $model = Mage::getModel("zolagopayment/vendor_invoice")->load($id);
            if (!$model->getId()) {
                Mage::throwException("Vendor Invoice not found");
            } elseif($model->getData('wfirma_invoice_id')) {
                Mage::throwException("Invoice already generated");
            } else {
                $wfirmaHlp->generateInvoice($model);
            }
            $this->_getSession()->addSuccess($this->_getHelper()->__("Invoice has been generated successfully"));

        } catch(GH_Wfirma_Exception $e) {
            $this->_getSession()->addError($wfirmaHlp->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($this->_getHelper()->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->_getHelper()->__("Some error occurred!"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*");
    }

    public function downloadAction()
    {
        $id = $this->getRequest()->getParam("id");
        /** @var GH_Wfirma_Helper_Data $wfirmaHlp */
        $wfirmaHlp = Mage::helper('ghwfirma');
        try {
            $wfirmaHlp->getVendorInvoice(null,$id);
        } catch(GH_Wfirma_Exception $e) {
            $this->_getSession()->addError($wfirmaHlp->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($this->_getHelper()->__($e->getMessage()));
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError($this->_getHelper()->__("Some error occurred!"));
            Mage::logException($e);
        }
        return true;
    }

    /**
     * @param $modelId
     * @return Zolago_Payment_Model_Vendor_Invoice
     */
    protected function _initModel($modelId)
    {
        if (Mage::registry('zolagopayment_current_invoice') instanceof Zolago_Payment_Model_Vendor_Invoice) {
            return Mage::registry('zolagopayment_current_invoice');
        }

        $model = Mage::getModel("zolagopayment/vendor_invoice");
        /* @var $model Zolago_Payment_Model_Vendor_Invoice */
        if ($modelId) {
            $model->load($modelId);
        }

        Mage::register('zolagopayment_current_invoice', $model);
        return $model;
    }

	/**
	 * @return Zolago_Payment_Helper_Data
	 */
	protected function _getHelper() {
		if(!$this->_helper) {
			$this->_helper = Mage::helper('zolagopayment');
		}
		return $this->_helper;
	}

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_invoice');
    }
}