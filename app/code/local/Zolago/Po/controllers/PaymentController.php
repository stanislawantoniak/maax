<?php

class Zolago_Po_PaymentController extends Zolago_Dropship_Controller_Vendor_Abstract
{
    public function createOverpaymentAction() {

        try {
            $poId = $this->getRequest()->getParam("id");//po_id
            /** @var Zolago_Po_Model_Po $po */
            $po = Mage::getModel("zolagopo/po")->load($poId);

            if ($po->getId()) {
                if (in_array($po->getUdropshipStatus(), Zolago_Po_Model_Po_Status::getFinishStatuses())) {
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("You can not assign overpayment to order with actual status"));
                }
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
                if (in_array($po->getUdropshipStatus(), Zolago_Po_Model_Po_Status::getFinishStatuses())) {
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("You can not assign overpayment to order with actual status"));
                }
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


    public function addPickUpPaymentAction(){

        try {
            $poId = $this->getRequest()->getParam("id");//po_id
            /** @var Zolago_Po_Model_Po $po */
            $po = Mage::getModel("zolagopo/po")->load($poId);

            if ($po->getId()) {
                if (!Mage::helper("zolagopo")->isPickUpPaymentCanBeEntered($po)) {
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("You can not Add Payment to order with actual status"));
                }

                if($po->isPaid()){
                    $this->_getSession()->addError(Mage::helper("zolagopo")->__("The order is already paid."));
                    $this->_redirect('udpo/vendor/edit', array('id' => $po->getId()));
                    return;
                }

                $amount = $this->getRequest()->getParam("payment_pickup_amount", 0);

                $amount = abs(round((float)$amount, 4));
                $debtAmount = abs($po->getDebtAmount());

                if($amount > $debtAmount){
                    $this->_getSession()->addError(Mage::helper("zolagopo")->__("Enter smaller amount."));
                    $this->_redirect('udpo/vendor/edit', array('id' => $po->getId()));
                    return;
                }

                /** @var Zolago_Payment_Helper_Data $helper */
                $helper = Mage::helper('zolagopayment');
                $txnId = "PICKUPPOINT_".$helper->RandomStringForRefund();


                if ($amount > 0) {
                    $txnType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER;
                    $order = $po->getOrder();
                    $status = Zolago_Payment_Model_Client::TRANSACTION_STATUS_COMPLETED;

                    /* @var $client Zolago_Payment_Model_Client */
                    $client = Mage::getModel("zolagopayment/client");
                    $client->saveTransaction(
                        $order,
                        $amount,
                        $status,
                        $txnId,
                        $txnType
                    );
                    $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Pick Up payment created"));

					Mage::dispatchEvent('zolagopayment_add_pickup_payment_transaction_save_after', array(
						"po" => $po,
						"amount" => $amount,
					));
                }

            } else {
                throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("There is no such PO"));
            }
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occurred."));
        }
        $this->_redirect('udpo/vendor/edit', array('id' => $po->getId()));
        return;
    }
}