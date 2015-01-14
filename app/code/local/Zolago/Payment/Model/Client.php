<?php
abstract class Zolago_Payment_Model_Client {
	const TRANSACTION_STATUS_NEW = 1;
	const TRANSACTION_STATUS_COMPLETED = 2;
	const TRANSACTION_STATUS_REJECTED = 3;


	protected function _connect() {
		return;
	}

	/**
	 * @param Mage_Sales_Model_Order $order
	 * @param float $amount
	 * @param int $status
	 * @param int $providerId
	 * @param string $txnId
	 * @param string $txnType
	 * @param array $data
	 * @return bool|int
	 */
	public function saveTransaction($order,$amount,$status,$txnId,$txnType,$data=array()) {
		if($order instanceof Mage_Sales_Model_Order
			&& is_numeric($amount)
			&& $txnId
			&& $this->validateTransactionStatus($status)
			&& $this->validateTransactionType($txnType)
		) {

			$is_closed = $status == self::TRANSACTION_STATUS_NEW ? 0 : 1; // transaction is closed when status is other than new

			$customerId = !$order->getCustomerIsGuest() ? $order->getCustomerId() : 0; //0 for guest

			/** @var Mage_Sales_Model_Order_Payment $payment */
			$paymentId = $order->getPayment()->getEntityId();

			/** @var Mage_Sales_Model_Order_Payment_Transaction $transaction */
			$transaction = Mage::getModel("sales/order/payment_transaction");
			$transaction
				->setOrder($order)
				->setOrderId($order->getId())
				->setPaymentId($paymentId)
				->setTxnId($txnId)
				->setTxnType($txnType)
				->setIsClosed($is_closed)
				->setTxnAmount($amount)
				->setTxnStatus($status)
				->setCustomerId($customerId);

			foreach($data as $key=>$value) {
				$transaction->setAdditionalInformation($key,$value);
			}
			Mage::log("trying to save...");
			$transaction->save();
			Mage::log("saved!");

			if($transaction->getId()) {
				return $transaction->getId();
			}
		}
		return false;
	}

	public function getTransactionsToUpdate($providerId) {
		$transactions = Mage::getResourceModel("zolagosales/payment_transaction")->getCollection()
			->addFieldToFilter('is_closed',0)
			->addFieldToFilter('payment_id',$providerId)
			->load();
		return $transactions;
	}

	protected function validateTransactionStatus($status) {
		if($status == self::TRANSACTION_STATUS_NEW ||
			$status == self::TRANSACTION_STATUS_COMPLETED ||
			$status == self::TRANSACTION_STATUS_REJECTED) {
			return true;
		}
		return false;
	}

	protected function validateTransactionType($type) {
		if($type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_PAYMENT ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND ||
			$type == Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID) {
			return true;
		}
		return false;
	}

}