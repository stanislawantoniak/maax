<?php

/**
 * Class Zolago_Payment_Adminhtml_Vendor_PaymentController
 */
class Zolago_Payment_Adminhtml_Vendor_PaymentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Vendor Payment Grid
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
            $model = Mage::getModel("zolagopayment/vendor_payment")->load($id);
            if (!$model->getId()) {
                // Default values for form
                $model->setDefaults();
            }
            $sessionData = $this->_getSession()->getFormData();
            if (!empty($sessionData)) {
                $model->addData($sessionData);
                $this->_getSession()->setFormData(null);
            }
            Mage::register("zolagopayment_current_payment", $model);
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
        $model = Mage::getModel("zolagopayment/vendor_payment");
        $helper = Mage::helper('zolagopayment');
        $data = $this->getRequest()->getParams();
        $modelId = $this->getRequest()->getParam("id");

        $this->_getSession()->setFormData(null);

        try {
            if ($this->getRequest()->isPost()) {
                $model->load($modelId);
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
                $this->_getSession()->addSuccess($helper->__("Vendor Payment Saved"));
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
            $model = Mage::getModel("zolagopayment/vendor_payment")->load($id);
            if (!$model->getId()) {
                throw new Mage_Core_Exception(Mage::helper('zolagopayment')->__("Vendor Payment not found"));
            }
            $model->delete();
            $this->_getSession()->addSuccess(Mage::helper('zolagopayment')->__("Vendor Payment Deleted"));
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            return $this->_redirectReferer();
        } catch (Exception $e) {
            $this->_getSession()->addError(Mage::helper('zolagopayment')->__("Some error occurred!"));
            Mage::logException($e);
        }
        return $this->_redirect("*/*");
    }

    /**
     * @param $modelId
     * @return Zolago_Payment_Model_Vendor_Payment
     */
    protected function _initModel($modelId)
    {
        if (Mage::registry('zolagopayment_current_payment') instanceof Zolago_Payment_Model_Vendor_Payment) {
            return Mage::registry('zolagopayment_current_payment');
        }

        $model = Mage::getModel("zolagopayment/vendor_payment");
        /* @var $model Zolago_Payment_Model_Vendor_Payment */
        if ($modelId) {
            $model->load($modelId);
        }

        Mage::register('zolagopayment_current_payment', $model);
        return $model;
    }

    /**
     * Acl check for this controller
     *
     * @return bool
     */
    protected function _isAllowed() {
        return Mage::getSingleton('admin/session')->isAllowed('admin/vendors/vendor_payment');
    }
}