<?php

class Zolago_Po_PaymentController extends Zolago_Dropship_Controller_Vendor_Abstract
{
    public function createOverpaymentAction() {

        try {
            $poId = $this->getRequest()->getParam("id");//po_id
            $po = Mage::getModel("zolagopo/po")->load($poId);

            if ($po->getId()) {
                /** @var Zolago_Payment_Model_Allocation $allocModel */
                $allocModel = Mage::getModel("zolagopayment/allocation");
                $error = $allocModel->createOverpayment($po);
                if (!$error) {
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Overpayment can not be created"));
                } else {
                    $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Overpayment created"));
                }
            } else {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("There is no such PO"));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
        $this->_redirect('udpo/vendor/edit', array('id' => $po->getId()));
        return;
    }


    public function allocateOverpaymentAction() {

        try {
            $poId = $this->getRequest()->getParam("id");//po_id
            $transactionId = $this->getRequest()->getParam("transaction_id");
            $po = Mage::getModel("zolagopo/po")->load($poId);

            if ($po->getId()) {
                /** @var Zolago_Payment_Model_Allocation $allocModel */
                $allocModel = Mage::getModel("zolagopayment/allocation");
                $error = $allocModel->allocateOverpayment($po, $transactionId);
                if (!$error) {
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Allocations overpayments can not be completed"));
                } else {
                    $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Allocations overpayments created"));
                }
            } else {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("There is no such PO"));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
        $this->_redirect('udpo/vendor/edit', array('id' => $po->getId()));
        return;
    }
}