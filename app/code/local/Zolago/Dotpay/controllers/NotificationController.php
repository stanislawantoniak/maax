<?php
require_once Mage::getConfig()->getModuleDir('controllers', 'Dotpay_Dotpay') . DS . "NotificationController.php";

class Zolago_Dotpay_NotificationController extends Dotpay_Dotpay_NotificationController
{
	public function indexAction()
	{
		$data = $this->getRequest()->getPost();

		if(isset($data['control']) && isset($data['operation_original_currency']) && isset($data['operation_original_amount'])) {
			/** @var Mage_Sales_Model_Order $order */
			$order = Mage::getModel('sales/order');
			$order->loadByIncrementId($data['control']);
			if (!$order->getId()) {
				die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
			}

			/** @var Zolago_Dotpay_Model_Client $client */
			$client = Mage::getModel("zolagodotpay/client", $order->getStore());
			if(isset($data['id']) && $data['id']) {
				$client->setDotpayId($data['id']);
			}

			if (!($order->getOrderCurrencyCode() == $data['operation_original_currency']
				&& round($order->getGrandTotal(), 2) == $data['operation_original_amount'])
			) {
				die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
			}

			//Save transaction
			$transaction = $client->saveTransactionFromPing($order, $data);
			if ($transaction !== false) {
				if ($data['operation_status'] == Zolago_Dotpay_Model_Client::DOTPAY_OPERATION_STATUS_COMPLETED &&
					$order->getStatus() != Mage_Sales_Model_Order::STATE_PROCESSING) {
					$order->addStatusHistoryComment(
						Mage_Sales_Model_Order::STATE_PROCESSING);
					$order->save();
				}
				die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_OK);
			}
			die(Zolago_Dotpay_Model_Client::DOTPAY_STATUS_ERROR);
		}
		$this->_redirect("/");
	}
}