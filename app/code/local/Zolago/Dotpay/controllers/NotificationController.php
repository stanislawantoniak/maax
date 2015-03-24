<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "NotificationController.php";

class Zolago_Dotpay_NotificationController extends Dotpay_Dotpay_NotificationController
{
	public function indexAction()
	{
		die('TEST');
		$data = $this->getRequest()->getPost();

		/** @var Mage_Sales_Model_Order $order */
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($data['control']);
		if (!$order->getId()) {
			Mage::log('WRONG ORDER',null,'dotpay.log');
			die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
		}

		/** @var Zolago_Dotpay_Model_Client $client */
		$client = Mage::getModel("zolagodotpay/client");

		if (!($order->getOrderCurrencyCode() == $data['operation_original_currency']
			&& round($order->getGrandTotal(), 2) == $data['operation_original_amount'])) {
			Mage::log('WORNG CURRENCY',null,'dotpay.log');
			die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
		}

		//Save transaction
		$transaction = $client->saveTransactionFromPing($order,$data);
		Mage::log('TRANSACTION OBJECT:',null,'dotpay.log');
		Mage::log($transaction,null,'dotpay.log');
		if($transaction !== false) {
			if ($data['operation_status'] == Zolago_Dotpay_Model_Client::DOTPAY_OPERATION_STATUS_COMPLETED) {
				$order->addStatusHistoryComment(
					Mage::helper('dotpay')->__('The payment has been accepted.'),
					Mage_Sales_Model_Order::STATE_PROCESSING);
				$order->save();
			}
			die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_OK);
		} else {
			die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
		}
	}
}