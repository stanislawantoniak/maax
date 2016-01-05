<?php

class Modago_Integrator_Model_Payment_Zolagopayment extends Mage_Payment_Model_Method_Abstract {

	const PAYMENT_METHOD_CODE = 'zolagopayment';

	protected $_code = self::PAYMENT_METHOD_CODE;
	protected $_isGateway = true;

}