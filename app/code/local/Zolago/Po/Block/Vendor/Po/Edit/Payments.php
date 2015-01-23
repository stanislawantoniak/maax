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


}
