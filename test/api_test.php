<?php
require_once('GH/Api/Model/SoapTest.php');

$fn = $_GET['fn'];
$param1 = $_GET['p1'];
$param2 = $_GET['p2'];
$param3 = $_GET['p3'];

$api = new GH_Api_Model_SoapTest();

switch($fn) {
	case 'doLogin':
		//doLogin($vendorId,$password,$webAPIKey)
		$api->doLogin($param1,$param2,$param3);
		break;

	case 'getChangeOrderMessage':
		//getChangeOrderMessage($sessionToken,$messageBatchSize,$messageType)
		$api->getChangeOrderMessage($param1,$param2,$param3);
		break;

	case 'setChangeOrderMessageConfirmation':
		//setChangeOrderMessageConfirmation($sessionToken,array $messageIDs)
		$api->setChangeOrderMessageConfirmation($param1,$param2);
		break;

	default:
		echo 'wrong fn name';
}