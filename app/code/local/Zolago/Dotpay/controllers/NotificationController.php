<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "NotificationController.php";

class Zolago_Dotpay_NotificationController extends Dotpay_Dotpay_NotificationController
{
	public function indexAction()
	{

		$data = $this->getRequest()->getPost();

		/** @var Mage_Sales_Model_Order $order */
		$order = Mage::getModel('sales/order');
		$order->loadByIncrementId($data['control']);
		if (!$order->getId()) {
			die('ERR');
		}

		/** @var Zolago_Dotpay_Model_Client $client */
		$client = Mage::getModel("zolagodotpay/client");

		if (!$client->validateData($data)) {
			die('ERR');
		}


		if (!($order->getOrderCurrencyCode() == $data['operation_original_currency']
			&& round($order->getGrandTotal(), 2) == $data['operation_original_amount'])) {
			die('ERR');
		}

		if ($data['operation_status'] == Zolago_Dotpay_Model_Client::DOTPAY_OPERATION_STATUS_COMPLETED) {
			$order->addStatusHistoryComment(
				Mage::helper('dotpay')->__('The payment has been accepted.'),
				Mage_Sales_Model_Order::STATE_PROCESSING);
			$order->save();
		}

		/* never cancel
				elseif ($this->getRequest()->getPost('t_status') == 3) {
					$order->cancel();
					$order->addStatusToHistory(
						Mage_Sales_Model_Order::STATE_CANCELED,
						Mage::helper('dotpay')->__('The order has been canceled.'));
				}
		*/

		//Save transaction
		$client->saveTransaction($order,$data);

		die('OK');
	}
}