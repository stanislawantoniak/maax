<?php

/**
 * Class Zolago_Po_Block_Vendor_Po_Edit_Payments
 */
class Zolago_Po_Block_Vendor_Po_Edit_Payments
    extends Zolago_Po_Block_Vendor_Po_Edit
{
    public function getOverpaymentUrl($action, $params=array()) {
        $params += array(
            "id"=> $this->getPo()->getId(),
            "form_key" => Mage::getSingleton('core/session')->getFormKey()
        );

        return $this->getUrl("udpo/payment/$action", $params);
    }


    public function isAllowed($resource) {
        return Mage::getSingleton('udropship/session')->isAllowed($resource);
    }


    public function getPickUpAddPaymentFormAction()
    {
        $params = array(
            "id" => $this->getPo()->getId(),
            "form_key" => Mage::getSingleton('core/session')->getFormKey(),
            "_secure" => true,
        );

        return $this->getUrl("udpo/payment/addPickUpPayment", $params);
    }


}
