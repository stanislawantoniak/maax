<?php

class Zolago_Payment_Model_Cc extends Zolago_Payment_Model_Abstract {

    const PAYMENT_METHOD_CODE = 'zolagopayment_cc';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code		  = self::PAYMENT_METHOD_CODE;
   
}