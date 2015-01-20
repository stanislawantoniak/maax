<?php

class Zolago_Po_Block_Vendor_Po_Edit_Payments
    extends Zolago_Po_Block_Vendor_Po_Edit
{
    public function getPaymentDetails()
    {
        $_po = $this->getPo();
        $_poId = $_po->getId();

        /* @var $allocationModel Zolago_Payment_Model_Allocation */
        $allocationModel = Mage::getModel('zolagopayment/allocation');
        $allocationModel->load($_poId, 'po_id');

        return $allocationModel;
    }
}
