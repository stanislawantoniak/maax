<?php

class Zolago_Dotpay_Model_Observer {

	public function updateTransactions() {

		$stores = Mage::app()->getStores();
		$dotpayIdsUpdated = array();
		/** @var Zolago_Dotpay_Model_Client $client */
		$client = Mage::getModel("zolagodotpay/client");

		foreach($stores as $store) {
			/** @var Mage_Core_Model_Store $store */
			$dotpayId = $store->getConfig(Zolago_Dotpay_Model_Client::DOTPAY_ID_CONFIG_PATH);
			if(in_array($dotpayId,$dotpayIdsUpdated)) {
				continue; //don't check same dotpay id transactions twice
			}
			$client->setStore($store);

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
				$order = Mage::getModel("sales/order")->loadByIncrementId($transaction->getOrderId());
				if($order->getId()) {
					$client->updateTransaction($order, $transaction->getTxnId(), Zolago_Payment_Model_Client::TRANSACTION_STATUS_REJECTED);
				}
			}

			$dotpayIdsUpdated[] = $dotpayId; //set as already updated number.
		}
	}
	
    /**
     * set field "method_name" as payment method name
     */
     public function setPaymentMethodName($observer) {
     	$obj = $observer->getEvent()->getOrderPaymentTransaction();
     	$paymentId = $obj->getPaymentId();
     	$method = Mage::getModel('sales/order_payment')->load($paymentId)->getMethod();
     	if ($obj->isObjectNew()) {
     		$obj->setPaymentMethod($method);
     	}
     }
}