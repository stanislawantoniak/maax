<?php

class Zolago_Payment_Model_Source_Channel_Owner
{
    const OWNER_MALL     = 1;
    const OWNER_VENDOR   = 0;

    public function toOptionArray()
    {
        /** @var Zolago_Payment_Helper_Data $paymentHelper */
        $paymentHelper = Mage::helper('zolagopayment');

        $arr = array(
            array('value' => self::OWNER_MALL  , 'label' => $paymentHelper->__('Mall')),
            array('value' => self::OWNER_VENDOR, 'label' => $paymentHelper->__('Vendor')),
        );
        return $arr;
    }
}
