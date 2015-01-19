<?php
include("init.php");
Mage::app('default');
/** @var Zolago_Dotpay_Model_Client $model */
$model = Mage::getModel("zolagodotpay/client");
try {
	echo "<pre>";
	//var_dump($model->dotpayCurl("operations","M1493-9522","mark_as_complete",array(),true,"DELETE"));
	$res = $model->getDotpayTransactionFromApi("M1493-9522");
	var_dump($res);
} catch(Exception $e) {
	Mage::logException($e);
}