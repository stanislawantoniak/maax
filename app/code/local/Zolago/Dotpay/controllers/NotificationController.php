<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "NotificationController.php";

class Zolago_Dotpay_NotificationController extends Dotpay_Dotpay_NotificationController
{
	public function indexAction()
	{
		$data = $this->getRequest()->getPost();

		Mage::log('DATA:', null, 'dotpay.log');
		Mage::log($data, null, 'dotpay.log');

		if(isset($data['control'])) {
			/** @var Mage_Sales_Model_Order $order */
			$order = Mage::getModel('sales/order');
			$order->loadByIncrementId($data['control']);
			if (!$order->getId()) {
				Mage::log('WRONG ORDER', null, 'dotpay.log');
				die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
			}
		} else {
			die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
		}

		if(isset($data['operation_original_currency']) && isset($data['operation_original_amount'])) {
			if (!($order->getOrderCurrencyCode() == $data['operation_original_currency']
				&& round($order->getGrandTotal(), 2) == $data['operation_original_amount'])
			) {
				Mage::log('WORNG CURRENCY:', null, 'dotpay.log');
				Mage::log('1ST CHECK:' . ($order->getOrderCurrencyCode() != $data['operation_original_currency'] ? 'FAILED' : 'OK'), null, 'dotpay.log');
				Mage::log('2ND CHECK:' . (round($order->getGrandTotal(), 2) == $data['operation_original_amount'] ? 'FAILED' : 'OK'), null, 'dotpay.log');
				die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
			}
		}

		/** @var Zolago_Dotpay_Model_Client $client */
		$client = Mage::getModel("zolagodotpay/client");
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