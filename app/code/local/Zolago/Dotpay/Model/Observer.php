<?php

class Zolago_Dotpay_Model_Observer {

	public function updateTransactions() {
		/** @var Zolago_Dotpay_Model_Client $client */
		$client = Mage::getModel("zolagodotpay/client");
		$transactions = $client->getDotpayTransactionsToUpdate();
		foreach($transactions as $transaction) {
			$transactionUpdate = $client->getDotpayTransactionUpdateFromApi($transaction->getTxnId());
			if($transaction->getTxnStatus() != $transactionUpdate['txnStatus']) {
				$order = Mage::getModel("sales/order")->loadByIncrementId($transactionUpdate['orderId']);
				if($order->getId()) {
					$client->updateTransaction($order, $transactionUpdate['txnId'], $transactionUpdate['txnStatus']);
				}
			}
		}

		$transactionsToCancel = $client->getDotpayTransactionsToCancel();
		foreach($transactionsToCancel as $transaction) {
			$order = Mage::getModel("sales/order")->loadById($transaction->getOrderId());
			if($order->getId()) {
				$client->updateTransaction($order, $transaction->getTxnId(), Zolago_Payment_Model_Client::TRANSACTION_STATUS_REJECTED);
			}
		}
	}
}