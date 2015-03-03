<?php

class Zolago_Payment_Model_Gateway extends Zolago_Payment_Model_Abstract {

    const PAYMENT_METHOD_CODE = 'zolagopayment_gateway';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code		  = self::PAYMENT_METHOD_CODE;
   
}