<?php
	class Zolago_Adminhtml_PaymentController extends Mage_Adminhtml_Controller_Action {

		public function refundAction() {
			$transactions = $this->getRequest()->getParam('txn');

			foreach($transactions as $txnId) {
				$transaction = Mage::getModel("sales/order_payment_transaction")->load($txnId);
				var_dump($transaction);
//				$transaction->setOrderPaymentObject($order->getPayment());
//				$transaction->loadByTxnId($txnId);
			}
			/** @var Zolago_Dotpay_Model_Client $dotpay */
			/*$dotpay = Mage::getModel("zolagodotpay/client");

			$request = $this->getRequest();
			$orderId = $request->getParam('orderid');*/
			/** @var Zolago_Sales_Model_Order $order */
			/*$order = Mage::getModel("sales/order")->load($orderId);

			$txnId = $request->getParam('txnid');

			$dotpay->makeRefund($order,$txnId);*/
		}
	}