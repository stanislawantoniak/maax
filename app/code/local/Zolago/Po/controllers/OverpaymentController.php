<?php

class Zolago_Po_OverpaymentController extends Zolago_Dropship_Controller_Vendor_Abstract
{
    public function createOverpaymentAction() {
        try {
            $udpo = $this->_registerPo();
            /** @var Zolago_Payment_Model_Allocation $allocModel */
            $allocModel = Mage::getModel("zolagopayment/allocation");
            $error = $allocModel->createOverpayment($udpo);
//            Mage::log("error:" . ($error ? "1" : "0"), null, "op.log");
            if (!$error) {
                if (!Mage::getSingleton("zolagodropship/session")->isOperatorMode()) {
                    //no needed because of Acl ?
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("You need to be operator to do overpayment"));
                } else {
                    throw new Mage_Core_Exception(Mage::helper("zolagopo")->__("Overpayment can not be created"));
                }
            } else {
                $this->_getSession()->addSuccess(Mage::helper("zolagopo")->__("Overpayment created"));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            Mage::logException($e);
            $this->_getSession()->addError(Mage::helper("zolagopo")->__("Some error occured."));
        }
        $this->_redirect('udpo/vendor/edit', array('id' => $udpo->getId()));
        return;

//        return $this->_redirectReferer();
    }

}